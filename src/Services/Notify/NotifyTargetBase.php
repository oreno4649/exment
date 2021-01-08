<?php
namespace Exceedone\Exment\Services\Notify;

use Illuminate\Support\Collection;
use Exceedone\Exment\Enums\NotifyActionTarget;
use Exceedone\Exment\Model\Notify;
use Exceedone\Exment\Model\CustomValue;
use Exceedone\Exment\Model\WorkflowAction;
use Exceedone\Exment\Model\WorkflowValue;

abstract class NotifyTargetBase
{
    /**
     * Notify
     *
     * @var Notify
     */
    protected $notify;

    /**
     * Notify action setting
     *
     * @var array
     */
    protected $action_setting;

    public function __construct(Notify $notify, array $action_setting)
    {
        $this->notify = $notify;
        $this->action_setting = $action_setting;
    }

    /**
     * Create instance
     *
     * @param string $notify_action_target
     * @param Notify $notify model
     * @return NotifyTargetBase|null
     */
    public static function make(string $notify_action_target, Notify $notify, array $action_setting) : ?NotifyTargetBase
    {
        switch($notify_action_target){
            case NotifyActionTarget::CREATED_USER:
                return new CreatedUser($notify, $action_setting);
            case NotifyActionTarget::HAS_ROLES:
                return new HasRoles($notify, $action_setting);
            case NotifyActionTarget::WORK_USER:
                return new WorkUser($notify, $action_setting);
            case NotifyActionTarget::FIXED_EMAIL:
                return new FixedEmail($notify, $action_setting);  
            case NotifyActionTarget::CUSTOM_COLUMN:
                return new Column($notify, $action_setting, $notify_action_target);  
        }

        return new Column($notify, $action_setting, $notify_action_target);  
    }

    


    /**
     * Get notify target model
     *
     * @param CustomValue $custom_value
     * @return Collection
     */
    abstract public function getModels(CustomValue $custom_value) : Collection;


    /**
     * Get notify target model for workflow
     *
     * @param CustomValue $custom_value
     * @return Collection
     */
    public function getModelsWorkflow(CustomValue $custom_value, WorkflowAction $workflow_action, ?WorkflowValue $workflow_value, $statusTo) : Collection
    {

        return collect();
    }
}
