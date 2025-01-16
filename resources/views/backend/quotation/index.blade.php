@extends('backend.layout.main') @section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}
        </div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    <section>
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">{{ trans('file.Quotation List') }}</h3>
                </div>
                {!! Form::open(['route' => 'quotations.index', 'method' => 'get']) !!}
                <div class="row mb-3">
                    <div class="col-md-4 offset-md-2 mt-3">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('file.Choose Your Date') }}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input type="text" class="daterangepicker-field form-control"
                                        value="{{ $starting_date }} To {{ $ending_date }}" required />
                                    <input type="hidden" name="starting_date" value="{{ $starting_date }}" />
                                    <input type="hidden" name="ending_date" value="{{ $ending_date }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3 @if (\Auth::user()->role_id > 2) {{ 'd-none' }} @endif">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('file.Choose Warehouse') }}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control"
                                    data-live-search="true" data-live-search-style="begins">
                                    <option value="0">{{ trans('file.All Warehouse') }}</option>
                                    @foreach ($lims_warehouse_list as $warehouse)
                                        @if ($warehouse->id == $warehouse_id)
                                            <option selected value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @else
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mt-3">
                        <div class="form-group">
                            <button class="btn btn-primary" id="filter-btn"
                                type="submit">{{ trans('file.submit') }}</button>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            @if (in_array('quotes-add', $all_permission))
                <a href="{{ route('quotations.create') }}" class="btn btn-info"><i class="dripicons-plus"></i>
                    {{ trans('file.Add Quotation') }}</a>&nbsp;
            @endif
        </div>
        <div class="table-responsive">
            <table id="quotation-table" class="table quotation-list" style="width: 100%">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Date') }}</th>
                        <th>{{ trans('file.Image') }} 1</th>
                        <th>{{ trans('file.Image') }} 2</th>
                        <th>{{ trans('file.reference') }}</th>
                        <th>{{ trans('file.Warehouse') }}</th>
                        <th>{{ trans('file.Biller') }}</th>
                        <th>{{ trans('file.customer') }}</th>
                        <th>{{ trans('file.Supplier') }}</th>
                        <th>{{ trans('file.Quotation Status') }}</th>
                        <!-- Thêm các cột mới -->
                        <th>{{ trans('file.Payment Method') }}</th>
                        <th>{{ trans('file.grand total') }}</th>
                        <th>{{ trans('file.Paid Amount') }}</th>
                        <th>{{ trans('file.Debt Amount') }}</th>
                        <!-- End các cột mới -->

                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>

                <tfoot class="tfoot active">
                    <th></th>
                    <th>{{ trans('file.Total') }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tfoot>
            </table>

        </div>
    </section>

    <div id="quotation-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog modal-fullscreen"> <!-- Thêm lớp "modal-fullscreen" -->
            <div class="modal-content">
                <div class="container mt-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-md-6 d-print-none">
                            <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i
                                    class="dripicons-print"></i> {{ trans('file.Print') }}</button>
                            {{ Form::open(['route' => 'quotation.sendmail', 'method' => 'post', 'class' => 'sendmail-form']) }}
                            <input type="hidden" name="quotation_id">
                            <button class="btn btn-default btn-sm d-print-none"><i class="dripicons-mail"></i>
                                {{ trans('file.Email') }}</button>
                            {{ Form::close() }}
                        </div>

                        <div class="col-md-6 d-print-none">
                            <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close"
                                class="close d-print-none"><span aria-hidden="true"><i
                                        class="dripicons-cross"></i></span></button>
                        </div>
                        <div class="col-md-12 " style="margin-left: 330px;">
                            <img src="{{ url('logo', $general_setting->site_logo) }}" height="70" width="120">
                        </div>
                        <div class="col-md-12 " style="font-size: 40px; margin-left: 200px;">
                            <i ></i>Báo giá/Đơn đặt hàng
                        </div>

                    </div>
                </div>
                <div id="quotation-content" class="modal-body">
                </div>
                <br>
                <table class="table table-bordered product-quotation-list">
                    <thead>
                        <th>#</th>
                        <th>{{ trans('file.product') }}</th>
                        <th>{{ trans('file.Image') }}  1</th>
                        <th>{{ trans('file.Image')  }} 2</th>
                        <th>Qty</th>
                        <th>{{ trans('file.Unit Price') }}</th>
                        {{-- <th>{{ trans('file.Tax') }}</th>
                        <th>{{ trans('file.Discount') }}</th> --}}
                        <th>{{ trans('file.Subtotal') }}</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div id="quotation-footer" class="modal-body"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $("ul#quotation").siblings('a').attr('aria-expanded', 'true');
        $("ul#quotation").addClass("show");
        $("ul#quotation #quotation-list-menu").addClass("active");

        $(".daterangepicker-field").daterangepicker({
            callback: function(startDate, endDate, period) {
                var starting_date = startDate.format('YYYY-MM-DD');
                var ending_date = endDate.format('YYYY-MM-DD');
                var title = starting_date + ' To ' + ending_date;
                $(this).val(title);
                $('input[name="starting_date"]').val(starting_date);
                $('input[name="ending_date"]').val(ending_date);
            }
        });

        var all_permission = <?php echo json_encode($all_permission); ?>;
        var quotation_id = [];
        var user_verified = <?php echo json_encode(env('USER_VERIFIED')); ?>;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

        $(document).on("click", "tr.quotation-link td:not(:first-child, :last-child)", function() {
            var quotation = $(this).parent().data('quotation');
            quotationDetails(quotation);
        });

        $(document).on("click", ".view", function() {
            var quotation = $(this).parent().parent().parent().parent().parent().data('quotation');
            quotationDetails(quotation);
        });

        $("#print-btn").on("click", function(e) {
            e.preventDefault();

            // Lưu trữ giá trị từ các input
            var customerName = $("#customer_name").val();
            var customerPhone = $("#customer_phone").val();
            var customerAddress = $("#customer_address").val();
            var customerCity = $("#customer_city").val();

            // Tạm thời thay thế các input bằng text để in
            var $customerInputs = $('.col-md-6:eq(1) input').hide();
            var $customerInfo = $('.col-md-6:eq(1)').append(
                customerName + '<br>' +
                customerPhone + '<br>' +
                customerAddress + '<br>' +
                customerCity
            );

            // Gọi lệnh in của trình duyệt
            window.print();

            // Sau khi in xong, khôi phục lại các input
            setTimeout(function() {
                $('.col-md-6:eq(1) br').remove();
                $('.col-md-6:eq(1)').contents().filter(function() {
                    return this.nodeType === 3; // Node.TEXT_NODE
                }).remove();
                $customerInputs.show();
            }, 100);
        });


        var starting_date = $("input[name=starting_date]").val();
        var ending_date = $("input[name=ending_date]").val();
        var warehouse_id = $("#warehouse_id").val();
        $('#quotation-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "quotations/quotation-data",
                data: {
                    all_permission: all_permission,
                    starting_date: starting_date,
                    ending_date: ending_date,
                    warehouse_id: warehouse_id
                },
                dataType: "json",
                type: "post",
                /*success:function(data){
                    console.log(data);
                }*/
            },
            "createdRow": function(row, data, dataIndex) {
                $(row).addClass('quotation-link');
                $(row).attr('data-quotation', data['quotation']);
            },
            "columns": [{
                    "data": "key"
                },
                {
                    "data": "date"
                },
                {
                    "data": "document"
                },
                {
                    "data": "image"
                },

                {
                    "data": "reference_no"
                },
                {
                    "data": "warehouse"
                },
                {
                    "data": "biller"
                },
                {
                    "data": "customer"
                },
                {
                    "data": "supplier"
                },
                {
                    "data": "status"
                },
                {
                    "data": "paying_method"
                },
                {
                    "data": "grand_total"
                },
                {
                    "data": "paid_amount"
                },
                {
                    "data": "debt"
                },
                {
                    "data": "options"
                },
            ],
            'language': {
                /*'searchPlaceholder': "{{ trans('file.Type date or quotation reference...') }}",*/
                'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ trans('file.Search') }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            order: [
                ['1', 'desc']
            ],
            'columnDefs': [{
                    "orderable": false,
                    'targets': [0, 3, 4, 7, 8, 9]
                },
                {
                    'render': function(data, type, row, meta) {
                        if (type === 'display') {
                            data =
                                '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    'checkboxes': {
                        'selectRow': true,
                        'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                    },
                    'targets': [0]
                }
            ],
            'select': {
                style: 'multi',
                selector: 'td:first-child'
            },
            'lengthMenu': [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            dom: '<"row"lfB>rtip',
            buttons: [{
                    extend: 'pdf',
                    text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'excel',
                    text: '<i title="export to excel" class="dripicons-document-new"></i>',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'csv',
                    text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'print',
                    text: '<i title="print" class="fa fa-print"></i>',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    text: '<i title="delete" class="dripicons-cross"></i>',
                    className: 'buttons-delete',
                    action: function(e, dt, node, config) {
                        if (user_verified == '1') {
                            quotation_id.length = 0;
                            $(':checkbox:checked').each(function(i) {
                                if (i) {
                                    var quotation = $(this).closest('tr').data('quotation');
                                    quotation_id[i - 1] = quotation[13];
                                }
                            });
                            if (quotation_id.length && confirm("Are you sure want to delete?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: 'quotations/deletebyselection',
                                    data: {
                                        quotationIdArray: quotation_id
                                    },
                                    success: function(data) {
                                        alert(data);
                                        //dt.rows({ page: 'current', selected: true }).deselect();
                                        dt.rows({
                                            page: 'current',
                                            selected: true
                                        }).remove().draw(false);
                                    }
                                });
                            } else if (!quotation_id.length)
                                alert('Nothing is selected!');
                        } else
                            alert('This feature is disable for demo!');
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i title="column visibility" class="fa fa-eye"></i>',
                    columns: ':gt(0)'
                },
            ],
            drawCallback: function() {
                var api = this.api();
                datatable_sum(api, false);
            }
        });

        function datatable_sum(dt_selector, is_calling_first) {
            if (dt_selector.rows('.selected').any() && is_calling_first) {
                var rows = dt_selector.rows('.selected').indexes();

                $(dt_selector.column(13).footer()).html(dt_selector.cells(rows, 13, {
                    page: 'current'
                }).data().sum().toFixed({{ $general_setting->decimal }}));
            } else {
                $(dt_selector.column(13).footer()).html(dt_selector.cells(rows, 13, {
                    page: 'current'
                }).data().sum().toFixed({{ $general_setting->decimal }}));
            }
        }

        if (all_permission.indexOf("quotes-delete") == -1)
            $('.buttons-delete').addClass('d-none');

        function quotationDetails(quotation) {
            // Lấy trực tiếp giá trị từ quotation array mà không cần .map()
            $('input[name="quotation_id"]').val(quotation[13]);

            // Tạo layout 2 cột cho thông tin chung
            var htmltext = '<div class="row" style="margin-left: 140px;">';

            // Cột trái - Thông tin cơ bản
            htmltext += '<div class="col-md-12">' +
                '<strong> Ngày Báo Giá: </strong>' + quotation[0]  + " |" +  '<strong> Số Báo Giá: </strong>' + quotation[1]
             ;




            htmltext += '</div>';

            // Cột phải - Thông tin thanh toán
            htmltext += '<div class="col-md-6">' +


                '</div></div>';

            // Thông tin người gửi/nhận


            htmltext += `
    <br>
<div class="delivery-info" style="
    margin-left: -16px;
    margin-right: -16px;
">
    <div class="row">
        <!-- Thông tin người nhận -->
        <div class="col-md-6">
            <div class="bordered-box customer">
                <strong>Bên mua </strong><br>
                <strong>Tên </strong> ${quotation[9]}<br>
                <strong>Email </strong> ${quotation[30]}<br>
                <strong>Số điện thoại </strong> ${quotation[10]}<br>
                <strong>Địa chỉ </strong> ${quotation[11]}, ${quotation[12]}<br>
                <strong>Mã số thuế </strong>NA<br>
            </div>
        </div>
        <!-- Thông tin người gửi -->
        <div class="col-md-6">
            <div class="bordered-box customer">
                <strong>Bên bán </strong><br>
                <strong>Đại diện </strong> Trần Nguyễn Anh Huy<br>
                <strong>Công ty </strong> Công ty TNHH SX-TM-DV ARES<br>
                <strong>Địa chỉ </strong> 391/19 Trần Hưng Đạo, P.Cầu Kho,Q1.HCM<br>
                <strong>Mã số thuế </strong>0317211560<br>

            </div>
        </div>

    </div>

</div>


`;
            // API call để lấy thông tin sản phẩm
            $.get('quotations/product_quotation/' + quotation[13], function(data) {
                $(".product-quotation-list tbody").remove();
                var name_code = data[0];
                var qty = data[1];
                var unit_code = data[2];
                var tax = data[3];
                var tax_rate = data[4];
                var discount = data[5];
                var subtotal = data[6];
                var batch_no = data[7];
                var newBody = $("<tbody>");

                 $.each(name_code, function(index) {
        var newRow = $("<tr>");
        var cols = '';
        cols += '<td><strong>' + (index + 1) + '</strong></td>';
        cols += '<td>' + name_code[index] + '</td>';
        cols += '<td>' + quotation[28] + '</td>';
        cols += '<td>' + quotation[29] + '</td>';
        cols += '<td>' + qty[index] + ' ' + unit_code[index] + '</td>';
        cols += '<td>' + parseFloat(subtotal[index] / qty[index]).toFixed(
            {{ $general_setting->decimal }}) + '</td>';
        cols += '<td>' + subtotal[index] + '</td>';
        newRow.append(cols);
        newBody.append(newRow);
    });
                // Thêm các dòng tổng
                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=4><strong>Tổng cộng chưa VAT:</strong></td>';
                cols += '<td>' + quotation[14] + '</td>'; // Total tax
                cols += '<td>' + quotation[15] + '</td>'; // Total discount
                cols += '<td>' + quotation[16] + '</td>'; // Total price
                newRow.append(cols);
                newBody.append(newRow);

                // Thêm dòng Paid Amount (Số tiền đã thanh toán)
             // Tạo hàng chứa tiêu đề "Phương thức thanh toán"
newRow = $("<tr>");
cols = '';
cols += '<td colspan=7 style="font-weight: bold; background-color: #f2f2f2; text-align: left;">Phương thức thanh toán</td>';
newRow.append(cols);
newBody.append(newRow);

// Thêm nội dung "Đợt 1"
newRow = $("<tr>");
cols = '';
cols += '<td colspan=6 style="padding-left: 20px;"><strong>Đợt 1: Thanh toán tạm ứng cọc 50% giá trị đơn đặt hàng:</strong></td>';
cols += '<td style="text-align: right;">' + quotation[23] + '</td>'; // Paid Amount
newRow.append(cols);
newBody.append(newRow);

// Thêm nội dung "Đợt 2"
newRow = $("<tr>");
cols = '';
cols += '<td colspan=6 style="padding-left: 20px;"><strong>Đợt 2: Thanh toán 50% giá trị còn lại bằng tiền mặt khi nhận hàng (COD) hoặc chuyển khoản thanh toán trước phần còn lại trước khi hàng xuất kho:</strong></td>';
cols += '<td style="text-align: right;">' + quotation[24] + '</td>'; // Paid Amount
newRow.append(cols);
newBody.append(newRow);


                // // Thêm dòng Debt Amount (Số tiền còn nợ)
                // newRow = $("<tr>");
                // cols = '';
                // cols += '<td colspan=8 class="text-danger"><strong>{{ trans('file.Debt Amount') }}:</strong></td>';
                // cols += '<td class="text-danger">' + quotation[24] + '</td>'; // Debt Amount
                // newRow.append(cols);
                // newBody.append(newRow);


                // Thêm dòng Payment Information (Thông tin thanh toán)
                newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=8>';
                cols += '<strong>Thông tin chuyển khoản:</strong><br>';
                cols += '- Chủ tài khoản: TRẦN NGUYỄN ANH HUY<br>';
                cols += '- Số tài khoản: 0071000879926<br>';
                cols += '- Ngân hàng: VIETCOMBANK';
                cols += '</td>';
                newRow.append(cols);
                newBody.append(newRow);

                newRow = $("<tr>");
cols = '';
cols += '<td colspan=8 style="color: brown;">';
cols += '<p>(*) Các thông tin về Size, Số áo sẽ chốt qua Zalo hoặc Email tùy yêu cầu khách hàng</p>';
cols += '<p>(**) Xuất hóa đơn VAT (Nếu có theo yêu cầu quý khách): Hóa đơn điện tử VAT sẽ được xuất qua Email từ 1-2 ngày sau khi đơn hàng hoàn tất</p>';
cols += '</td>';
newRow.append(cols);
newBody.append(newRow);


                $("table.product-quotation-list").append(newBody);
            });

            // Footer với ghi chú và thông tin người tạo
            var htmlfooter = `
    <hr>
    <div class="row mt-3">
        <div class="col-md-12">
            <strong>{{ trans('file.Note') }}:</strong> ${quotation[25]}<br><br>

        </div>
    </div>
    <div class="row mt-3">
        <div class="col ">
            <strong>ĐẠI DIỆN BÊN MUA</strong>
        </div>
        <div class="col " style="margin-left: 250px;">
            <strong>ĐẠI DIỆN BÊN BÁN</strong>
        </div>
    </div>
`;



            $('#quotation-content').html(htmltext);
            $('#quotation-footer').html(htmlfooter);
            $('#quotation-details').modal('show');
        }
    </script>
    <style>
        @media print {





            /* Định dạng input khi in */
            input {
                border: none !important;
                background: transparent !important;
                padding: 0 !important;
            }
        }

        .delivery-info .bordered-box {
            border: 1px solid #dee2e6;
            /* Khung viền đen */
            padding: 10px;
            /* Khoảng cách giữa nội dung và viền */
            border-radius: 5px;
            /* Góc bo tròn */
            background-color: #f9f9f9;
            /* Nền nhạt */
            margin-bottom: 10px;
            /* Khoảng cách dưới */
        }

        .delivery-info .row {
            display: flex;
            /* Đặt các phần tử con nằm ngang */
            justify-content: space-between;
            /* Căn đều khoảng cách giữa các phần tử */
            gap: 15px;
            /* Khoảng cách giữa các cột */
        }

        .delivery-info .col-md-6 {
            flex: 1;
            /* Các cột chiếm không gian bằng nhau */
            max-width: 48%;
            /* Đảm bảo mỗi cột không vượt quá 48% chiều rộng */
            box-sizing: border-box;
            /* Đảm bảo padding không làm thay đổi kích thước hộp */
        }

        .delivery-info .col-md-12 {
            flex: 1;
            /* Các cột chiếm không gian bằng nhau */
            max-width: 48%;
            /* Đảm bảo mỗi cột không vượt quá 48% chiều rộng */
            box-sizing: border-box;
            /* Đảm bảo padding không làm thay đổi kích thước hộp */
        }
    </style>
@endpush
