<?php

namespace Exceedone\Exment\Console;

use Encore\Admin\Grid;
use Illuminate\Console\Command;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\CustomView;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Services\DataImportExport;

class ExportCommand extends Command
{
    use CommandTrait, ExportCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exment:export {table_name} {--action=default} {--type=all} {--page=1} {--count=} {--format=csv} {--view=} {--dirpath=} {--add_setting=0} {--add_relation=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export Exment data';

    /**
     * full path stored export files.
     *
     * @var string
     */
    protected $dirpath;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->initExmentCommand();
    }

    protected function getParameters()
    {
        $table_name = $this->argument("table_name");

        if (!isset($table_name)) {
            throw new \Exception('parameter table name is empty');
        }

        $custom_table = CustomTable::getEloquent($table_name);

        if (!isset($custom_table)) {
            throw new \Exception('parameter table name error : ' . $table_name);
        }

        $options = $this->getParametersCommon();

        // get parameters
        $options['type'] = $this->option("type");
        $options['page'] = $this->option("page");
        $options['count'] = $this->option("count");
        $options['add_setting'] = $this->option("add_setting");
        $options['add_relation'] = $this->option("add_relation");

        if (!\in_array($options['type'], ['all', 'page'])) {
            throw new \Exception('optional parameter type error : ' . $options['type']);
        }

        if ($options['type'] == 'page') {
            if (!preg_match("/^[0-9]+$/", $options['page'])) {
                throw new \Exception('optional parameter page error : ' . $options['page']);
            }
            if (!isset($options['count'])) {
                $options['count'] = $options['view'] ? $options['view']->pager_count : System::grid_pager_count();
            } elseif (!preg_match("/^[0-9]+$/", $options['count'])) {
                throw new \Exception('optional parameter count error : ' . $options['count']);
            }
        }

        return [$custom_table, $options];
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // get parameters
            list($custom_table, $options) = $this->getParameters();
            $classname = getModelName($custom_table);
            
            $grid = new Grid(new $classname);
            if ($options['type'] == 'page') {
                $grid->model()->setPerPageArguments([$options['count'], ['*'], 'page', $options['page']])
                    ->disableHandleInvalidPage();
            } elseif ($options['type'] == 'all') {
                $grid->model()->usePaginate(false);
            }

            if (isset($options['view']) && $options['view'] instanceof CustomView) {
                $options['view']->filterModel($grid->model());
            }
    
            $service = (new DataImportExport\DataImportExportService())
                ->exportAction(new DataImportExport\Actions\Export\CustomTableAction(
                    [
                        'custom_table' => $custom_table,
                        'grid' => $grid,
                        'add_setting' => boolval(array_get($options, 'add_setting', false)),
                        'add_relation' => boolval(array_get($options, 'add_relation', false)),
                    ]
                ))->viewExportAction(new DataImportExport\Actions\Export\SummaryAction(
                    [
                        'custom_table' => $custom_table,
                        'custom_view' => $options['view'],
                        'grid' => $grid
                    ]
                ))
                ->format($options['format'])
                ->filebasename($custom_table->table_name);
            
            $result = $service->exportBackground($options);

            $message = array_get($result, 'message');
            if (!empty($message)) {
                $this->line($message);
            }
        } catch (\Exception $e) {
            \Log::error($e);
            $this->error($e->getMessage());
            return -1;
        }

        return 0;
    }
}
