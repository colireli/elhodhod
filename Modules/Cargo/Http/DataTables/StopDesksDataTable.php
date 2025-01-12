<?php

namespace Modules\Cargo\Http\DataTables;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Button;
use Modules\Cargo\Entities\StopDesk;
use Illuminate\Http\Request;
use Modules\Cargo\Http\Filter\StopDeskFilter;

class StopDesksDataTable extends DataTable
{
    public $table_id = 'stopdesks';
    public $btn_exports = [
        'excel',
        'print',
        'pdf'
    ];

    public $filters = [
        'state_id',
        'company_id',
    ];

    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     *
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->rawColumns(['action', 'select','name'])
            ->filterColumn('name', function($query, $keyword) {
                $query->where('name', 'LIKE', "%$keyword%");
            })
            ->filterColumn('state_id', function($query, $keyword) {
                $query->where('state_id', 'LIKE', "%$keyword%");
            })
            ->filterColumn('company_id', function($query, $keyword) {
                $query->where('company_id', 'LIKE', "%$keyword%");
            })
            ->orderColumn('name', function ($query, $order) {
                $query->orderBy('name', $order);
            })

            ->addColumn('select', function (StopDesk $model) {
                $adminTheme = env('ADMIN_THEME', 'adminLte');
                return view($adminTheme.'.components.modules.datatable.columns.checkbox', ['model' => $model, 'ifHide' => $model->id == 0]);
            })
            ->editColumn('id', function (StopDesk $model) {
                return '#'.$model->id;
            })
            ->editColumn('name', function (StopDesk $model) {
                return $model->name;
            })
            ->editColumn('reference', function (StopDesk $model) {
                return $model->reference;
            })
            ->editColumn('phone', function (StopDesk $model) {
                return $model->phone;
            })
            ->editColumn('state_id', function (StopDesk $model) {
                return $model->state->name;
            })
            ->editColumn('country_id', function (StopDesk $model) {
                return $model->country ? $model->country->name : '';
            })

            ->editColumn('created_at', function (StopDesk $model) {
                return date('d M, Y H:i', strtotime($model->created_at));
            })
            ->addColumn('action', function (StopDesk $model) {
                $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.stopdesks.columns.actions', ['model' => $model, 'table_id' => $this->table_id]);
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param  StopDesk  $model
     *
     * @return StopDesk
     */
    public function query(StopDesk $model, Request $request)
    {
        $query = $model->newQuery();

        // class filter for user only
        $stopdesk_filter = new StopDeskFilter($query, $request);

        $query = $stopdesk_filter->filterBy($this->filters);

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $lang = \LaravelLocalization::getCurrentLocale();
        $lang = get_locale_name_by_code($lang, $lang);

        return $this->builder()
            ->setTableId($this->table_id)
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->stateSave(true)
            ->orderBy(1)
            ->responsive()
            ->autoWidth(false)
            ->parameters([
                'scrollX' => true,
                'dom' => 'Bfrtip',
                'bDestroy' => true,
                'language' => ['url' => "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/{$lang}.json"],
                'buttons' => [
                    ...$this->buttonsExport(),
                ],
            ])
            ->addTableClass('align-middle table-row-dashed fs-6 gy-5');
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::computed('select')
                    ->title('
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input checkbox-all-rows" type="checkbox">
                        </div>
                    ')
                    ->responsivePriority(-1)
                    ->addClass('not-export')
                    ->width(50),
            Column::make('id')->title(__('cargo::view.table.#'))->width(50),
            Column::make('name')->title(__('stop desk')),
            Column::make('reference')->title(__('reference')),
            Column::make('phone')->title(__('phone')),
            Column::make('country_id')->title(__('cargo::view.country')),
            Column::make('state_id')->title(__('cargo::view.table.region')),
            Column::make('created_at')->title(__('view.created_at')),
            Column::computed('action')
                ->title(__('view.action'))
                ->addClass('text-center not-export')
                ->responsivePriority(-1)
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'packages_'.date('YmdHis');
    }


    /**
     * Transformer buttons export.
     *
     * @return string
     */
    protected function buttonsExport()
    {
        $btns = [];
        foreach($this->btn_exports as $btn) {
            $btns[] = [
                'extend' => $btn,
                'exportOptions' => [
                    'columns' => 'th:not(.not-export)'
                ]
            ];
        }
        return $btns;
    }
}
