<?php

use Illuminate\Database\Migrations\Migration;
use Exceedone\Exment\Database\View;
use Exceedone\Exment\Enums\SystemTableName;

class UserOrganizationView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \ExmentDB::createView(SystemTableName::VIEW_USER_ORGANIZATION, View\UserOrganizationView::createUserOrganizationView());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \ExmentDB::dropView(SystemTableName::VIEW_USER_ORGANIZATION);
        //
    }
}
