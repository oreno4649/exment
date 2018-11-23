<?php

namespace Exceedone\Exment\Controllers;

use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Model\LoginUser;
use Exceedone\Exment\Providers\CustomUserProvider;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Auth;
use Exceedone\Exment\Model\File as ExmentFile;

/**
 * For login controller
 */
class AuthController extends \Encore\Admin\Controllers\AuthController
{
    use AuthTrait;

    /**
     * Login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLoginExment(Request $request)
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view('exment::auth.login', $this->getLoginPageData());
    }   
    

    /**
     * Login page using provider (SSO).
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLoginProvider(Request $request, $login_provider)
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        // provider check
        $provider = config("services.$login_provider");
        if(!isset($provider)){
            abort(404);
        }
        $socialiteProvider = $this->getSocialiteProvider($login_provider);
        return $socialiteProvider->redirect();
    }

    /**
     * callback login provider and login exment
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function callbackLoginProvider(Request $request, $login_provider)
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }
        
        $socialiteProvider = $this->getSocialiteProvider($login_provider);
        $provider_user = $socialiteProvider->user();

        // check exment user
        $exment_user = getModelName(Define::SYSTEM_TABLE_NAME_USER)
            ::where('value->email', $provider_user->email)
            ->first();
        if(!isset($exment_user) && config("services.$login_provider.create_user", false) !== true){
            return back()->withInput()->withErrors([
                $this->username() => $this->getFailedLoginMessage(),
            ]);
        }
        if(!isset($exment_user)){
            return redirect(admin_base_path('auth/login'))->withInput()->withErrors([
                $this->username() => $this->getFailedLoginMessage(),
            ]);
        }

        // update user info
        $exment_user->setValue([
            'user_name' => $provider_user->name
        ]);
        $exment_user->save();

        $login_user = $this->getLoginUser($login_provider, $exment_user, $provider_user);
        
        if ($this->guard()->attempt(
            [
                'username' => $provider_user->email,
                'login_provider' => $login_provider,
                'password' => $provider_user->id,
            ]
        )) {
            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

    protected function getSocialiteProvider(string $login_provider){
        //config(["services.$login_provider.redirect" => admin_url(url_join("auth", "login", $login_provider, "callback"))]);
        config(["services.$login_provider.redirect" => "https://local-exment/admin/auth/login/graph/callback"]);

        return \Socialite::with($login_provider)->stateless();
    }
    
    protected function getLoginUser($login_provider, $exment_user, $provider_user){
        
        // get login_user
        $login_user = CustomUserProvider::RetrieveByCredential(
            [
                'username' => $provider_user->email,
                'login_provider' => $login_provider
            ]
        );
        if(isset($login_user)){
            // check password
            if(CustomUserProvider::ValidateCredential($login_user, [
                'password' => $provider_user->id
            ])){
                return $login_user;
            }
        }

        // if don't has, create loginuser
        $login_user = new LoginUser;
        $login_user->base_user_id = $exment_user->id;
        $login_user->login_provider = $login_provider;
        $login_user->password = bcrypt($provider_user->id);
        $login_user->save();
        return $login_user;
    }


    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $user = LoginUser::class;
        return $user::form(function (Form $form) {
            $form->display('base_user.value.user_code', exmtrans('user.user_code'));
            $form->text('base_user.value.user_name', exmtrans('user.user_name'));
            $form->email('base_user.value.email', exmtrans('user.email'));
            $form->image('avatar', exmtrans('user.avatar'))
                ->move('avatar')
                ->name(function($file){
                    $exmentfile = ExmentFile::saveFileInfo($this->getDirectory(), $file->getClientOriginalName());
                    return $exmentfile->filename;
                });
            $form->password('password', exmtrans('user.new_password'))->rules(get_password_rule(false))->help(exmtrans('user.help.change_only').exmtrans('user.help.password'));
            $form->password('password_confirmation', exmtrans('user.new_password_confirmation'));

            $form->setAction(admin_base_path('auth/setting'));
            $form->ignore(['password_confirmation']);
            disableFormFooter($form);
            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
                $tools->disableDelete();
            });

            $form->saving(function (Form $form) {
                // if not contains $form->password, return
                $form_password = $form->password;
                if (!isset($form_password)) {
                    $form->password = $form->model()->password;
                } elseif ($form_password && $form->model()->password != $form_password) {
                    $form->password = bcrypt($form_password);
                }
            });
            
            $form->saved(function ($form) {
                // saving user info
                DB::transaction(function () use ($form) {
                    $req = Req::all();

                    // login_user id
                    $user_id = $form->model()->base_user->id;
                    // save user name and email
                    $user = getModelName(Define::SYSTEM_TABLE_NAME_USER)::find($user_id);
                    $user->setValue([
                        'user_name' => array_get($req, 'base_user.value.user_name'),
                        'email' => array_get($req, 'base_user.value.email'),
                    ]);
                    $user->save();
                });
                
                admin_toastr(trans('admin.update_succeeded'));
    
                return redirect(admin_base_path('auth/setting'));
            });
        });
    }
}
