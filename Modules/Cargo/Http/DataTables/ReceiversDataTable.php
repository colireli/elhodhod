<?php

namespace Modules\Cargo\Http\DataTables;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Button;
use Modules\Cargo\Entities\Receiver;
use Illuminate\Http\Request;
use Modules\Cargo\Http\Filter\ReceiverFilter;

class ReceiversDataTable extends DataTable
{

    public $table_id = 'receivers';
    public $btn_exports = [
        'excel',
        'print',
        'pdf'
    ];
    public $filters = ['name' , 'reciver_address' , 'receiver_mobile' , 'created_at'];
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
            ->rawColumns(['action','name' ,'reciver_address' , 'receiver_mobile' ])

            // ->filterColumn('user', function($query, $keyword) {
            //     $query->where('name', 'LIKE', "%$keyword%");
            //     $query->orWhere('reciver_address', 'LIKE', "%$keyword%");
            //     $query->orWhere('receiver_mobile', 'LIKE', "%$keyword%");
            // })

            ->orderColumn('name', function ($query, $order) {
                $query->orderBy('name', $order);
            })

            ->orderColumn('reciver_address', function ($query, $order) {
                $query->orderBy('reciver_address', $order);
            })

            ->orderColumn('receiver_mobile', function ($query, $order) {
                $query->orderBy('receiver_mobile', $order);
            })

            ->editColumn('created_at', function (Receiver $model) {
                return date('d M, Y H:i', strtotime($model->created_at));
            })



            ->addColumn('action', function (Receiver $model) {
                $adminTheme = env('ADMIN_THEME', 'adminLte');return view('cargo::'.$adminTheme.'.pages.receicers.columns.actions', ['model' => $model, 'table_id' => $this->table_id]);
            });
    }


    public function query(Receiver $model, Request $request)
    {
        $query = $model->newQuery();

        // class filter for user only
        $receiver_filter = new ReceiverFilter($query, $request);

        $query = $receiver_filter->filterBy($this->filters);

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
                'language' => ['url' => "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/$lang.json"],
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
            Column::make('id')->title(__('cargo::view.table.#'))->width(50),
            Column::make('name')->title(__('cargo::view.receiver_name')),
            Column::make('reciver_address')->title(__('cargo::view.receiver_address')),
            Column::make('receiver_mobile')->title(__('cargo::view.receiver_phone')),
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
        return 'receivers_'.date('YmdHis');
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
