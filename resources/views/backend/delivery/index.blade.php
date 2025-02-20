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
        <div class="table-responsive">
            <table id="delivery-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Delivery Reference') }}</th>
                        <th>{{ trans('file.Sale Reference') }}</th>
                        <th>{{ trans('file.Ship Code') }}</th>
                        <th>{{ trans('file.customer') }}</th>
                        <th>{{ trans('file.Courier') }}</th>
                        <th>{{ trans('file.Address') }}</th>
                        <th>{{ trans('file.Products') }}</th>
                        <th>{{ trans('file.grand total') }}</th>
                        <th>{{ trans('file.Due') }}</th>
                        <th>{{ trans('file.Status') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_delivery_all as $key => $delivery)
                        <?php
                        $customer_sale = DB::table('sales')
                            ->join('customers', 'sales.customer_id', '=', 'customers.id')
                            ->where('sales.id', $delivery->sale_id)
                            ->select('sales.reference_no', 'customers.name', 'customers.phone_number', 'customers.city', 'sales.grand_total', 'sales.paid_amount')
                            ->get();

                        $product_names = DB::table('sales')
                            ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                            ->join('products', 'products.id', '=', 'product_sales.product_id')
                            ->where('sales.id', $delivery->sale_id)
                            ->pluck('products.name')
                            ->toArray();
                        if ($delivery->packing_slip_ids) {
                            $packing_slip_references = \App\Models\PackingSlip::whereIn('id', explode(',', $delivery->packing_slip_ids))
                                ->pluck('reference_no')
                                ->toArray();
                        } else {
                            $packing_slip_references[0] = 'N/A';
                        }

                        switch ($delivery->status) {
                            case 0:
                                $status = trans('file.All');
                                break;
                            case 1:
                                $status = trans('file.Delivered');
                                break;
                            case 2:
                                $status = trans('file.Successful Delivery');
                                break;
                            case 3:
                                $status = trans('file.Completed');
                                break;

                            default:
                                $status = trans('file.Unknown Status'); // Trạng thái không xác định
                                break;
                        }

                        $barcode = \DNS2D::getBarcodePNG($delivery->reference_no, 'QRCODE');
                        ?>
                        @if ($delivery->sale)
                            <tr class="delivery-link" data-barcode="{{ $barcode }}"
                                data-delivery='["{{ date($general_setting->date_format, strtotime($delivery->created_at->toDateString())) }}", "{{ $delivery->reference_no }}", "{{ $delivery->sale->reference_no }}", "{{ $status }}", "{{ $delivery->id }}", "{{ $delivery->sale->customer->name }}", "{{ $delivery->sale->customer->phone_number }}", "{{ $delivery->sale->customer->address }}", "{{ $delivery->sale->customer->city }}", "{{ $delivery->note }}", "{{ $delivery->user->name }}", "{{ $delivery->delivered_by }}", "{{ $delivery->recieved_by }}","{{ $delivery->recieved_phone }}","{{ $delivery->ship_code }}"]'>
                                <td>{{ $key }}</td>
                                <td>{{ $delivery->reference_no }}</td>
                                <td>{{ $customer_sale[0]->reference_no }}</td>
                                <td>{{$delivery->ship_code }}</td>
                                <td>{!! $customer_sale[0]->name . '<br>' . $customer_sale[0]->phone_number !!}</td>
                                @if ($delivery->courier_id)
                                    <td>{{ $delivery->courier->name }}</td>
                                @else
                                    <td>N/A</td>
                                @endif
                                <td>{{ $delivery->address }}</td>
                                <td>{{ implode(',', $product_names) }}</td>
                                <td>{{ number_format($customer_sale[0]->grand_total, 2) }}</td>
                                {{-- Tính nợ --}}
        @php
        $returned_amount = DB::table('returns')->where('sale_id', $delivery->sale->id)->sum('grand_total');
        $due = $customer_sale[0]->grand_total - $returned_amount - $customer_sale[0]->paid_amount;
    @endphp
    <td>{{ number_format($due, 2, ',', '.') }}</td>

                                <td>
                                    @switch($delivery->status)
                                        @case(0)
                                            <div class="badge badge-info">{{ $status }}</div>
                                        @break

                                        @case(1)
                                            <div class="badge badge-warning">{{ $status }}</div>
                                        @break

                                        @case(2)
                                            <div class="badge badge-primary">{{ $status }}</div>
                                        @break

                                        @case(3)
                                            <div class="badge badge-success">{{ $status }}</div>
                                        @break

                                        @case(4)
                                            <div class="badge badge-danger">{{ $status }}</div>
                                        @break

                                        @case(5)
                                            <div class="badge badge-dark">{{ $status }}</div>
                                        @break

                                        @case(6)
                                            <div class="badge badge-secondary">{{ $status }}</div>
                                        @break

                                        @case(7)
                                            <div class="badge badge-light">{{ $status }}</div>
                                        @break

                                        @case(8)
                                            <div class="badge badge-info">{{ $status }}</div>
                                        @break

                                        @default
                                            <div class="badge badge-default">{{ trans('file.Unknown Status') }}</div>
                                    @endswitch
                                </td>

                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">{{ trans('file.action') }}
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                            user="menu">
                                            <li>
                                                <button type="button" data-id="{{ $delivery->id }}"
                                                    class="open-EditCategoryDialog btn btn-link"><i
                                                        class="dripicons-document-edit"></i>
                                                    {{ trans('file.edit') }}</button>
                                            </li>
                                            <li class="divider"></li>
                                            {{ Form::open(['route' => ['delivery.delete', $delivery->id], 'method' => 'post']) }}
                                            <li>
                                                <button type="submit" class="btn btn-link"
                                                    onclick="return confirmDelete()"><i class="dripicons-trash"></i>
                                                    {{ trans('file.delete') }}</button>
                                            </li>
                                            {{ Form::close() }}
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- Modal -->
    <div id="delivery-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="container mt-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-md-6 d-print-none">
                            <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i
                                    class="dripicons-print"></i> {{ trans('file.Print') }}</button>

                            {{ Form::open(['route' => 'delivery.sendMail', 'method' => 'post', 'class' => 'sendmail-form']) }}
                            <input type="hidden" name="delivery_id">
                            <button class="btn btn-default btn-sm d-print-none"><i class="dripicons-mail"></i>
                                {{ trans('file.Email') }}</button>
                            {{ Form::close() }}
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close"
                                class="close d-print-none"><span aria-hidden="true"><i
                                        class="dripicons-cross"></i></span></button>
                        </div>
                        <div class="col-md-12">
                            <h3 id="exampleModalLabel" class="modal-title text-center container-fluid">
                                {{ $general_setting->site_title }}
                            </h3>
                        </div>
                        <div class="col-md-12 text-center">
                            <i style="font-size: 15px;">{{ trans('file.Delivery Details') }}</i>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered" id="delivery-content">
                        <tbody></tbody>
                    </table>
                    <br>
                    <table class="table table-bordered product-delivery-list">
                        <thead>
                            <th>No</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>{{ trans('file.Batch No') }}</th>
                            <th>{{ trans('file.Expired Date') }}</th>
                            <th>Qty</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div id="delivery-footer" class="row">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="edit-delivery" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Update Delivery') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'delivery.update', 'method' => 'post', 'files' => true]) !!}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Delivery Reference') }}</label>
                            <p id="dr"></p>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Sale Reference') }}</label>
                            <p id="sr"></p>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>{{ trans('file.Status') }} *</label>
                            <select name="status" class="form-control selectpicker">
                                <option value="1">{{ trans('file.Delivered') }}</option>
                                <option value="2">{{ trans('file.Successful Delivery') }}</option>
                                <option value="3">{{ trans('file.Completed') }}</option>

                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Courier') }}</label>
                            <select name="courier_id" id="courier_id" class="selectpicker form-control"
                                data-live-search="true" title="Select courier...">
                                @foreach ($lims_courier_list as $courier)
                                    <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mt-2 form-group">
                            <label>{{ trans('file.Delivered By') }}</label>
                            <input type="text" name="delivered_by" class="form-control">
                        </div>
                        <div class="col-md-6 mt-2 form-group">
                            <label>{{ trans('file.Recieved By') }}</label>
                            <input type="text" name="recieved_by" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.customer') }} *</label>
                            <p id="customer"></p>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Attach File') }}</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Address') }} *</label>
                            <textarea rows="3" name="address" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Note') }}</label>
                            <textarea rows="3" name="note" class="form-control"></textarea>
                        </div>
                    </div>
                    <input type="hidden" name="reference_no">
                    <input type="hidden" name="delivery_id">
                    <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        $("ul#sale").siblings('a').attr('aria-expanded', 'true');
        $("ul#sale").addClass("show");
        $("ul#sale #delivery-menu").addClass("active");

        var delivery_id = [];
        var user_verified = <?php echo json_encode(env('USER_VERIFIED')); ?>;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#print-btn").on("click", function() {
            var divContents = document.getElementById("delivery-details").innerHTML;
            var a = window.open('');
            a.document.write('<html>');
            a.document.write(
                '<body><style>body{font-family: sans-serif;line-height: 1.15;-webkit-text-size-adjust: 100%;}.d-print-none{display:none}.text-center{text-align:center}.row{width:100%;margin-right: -15px;margin-left: -15px;}.col-md-12{width:100%;display:block;padding: 5px 15px;}.col-md-6{width: 50%;float:left;padding: 5px 15px;}table{width:100%;margin-top:30px;}th{text-aligh:left}td{padding:10px}table,th,td{border: 1px solid black; border-collapse: collapse;}#delivery-footer{margin-left:10px}</style><style>@media print {.modal-dialog { max-width: 1000px;} }</style>'
                );
            a.document.write(divContents);
            a.document.write('</body></html>');
            a.document.close();
            setTimeout(function() {
                a.close();
            }, 10);
            a.print();
        });

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

        $(document).on("click", "tr.delivery-link td:not(:first-child, :last-child)", function() {
    var delivery = $(this).parent().data('delivery');

    var barcode = $(this).parent().data('barcode');
    deliveryDetails(delivery, barcode);
});

        function deliveryDetails(delivery, barcode) {
console.log(delivery);
            $('input[name="delivery_id"]').val(delivery[4]);
            $("#delivery-content tbody").remove();
            var newBody = $("<tbody>");
            var rows = '';
            rows += '<tr><td>Mã Vận Đơn</td><td>' + delivery[14] + '</td></tr>';
            rows += '<tr><td>Date</td><td>' + delivery[0] + '</td></tr>';
            rows += '<tr><td>Delivery Reference</td><td>' + delivery[1] + '</td></tr>';
            rows += '<tr><td>Sale Reference</td><td>' + delivery[2] + '</td></tr>';
            rows += '<tr><td>Status</td><td>' + delivery[3] + '</td></tr>';
            rows += '<tr><td>Customer Name</td><td>' + delivery[5] + '</td></tr>';
            rows += '<tr><td>Address</td><td>' + delivery[7] + ', ' + delivery[8] + '</td></tr>';
            rows += '<tr><td>Phone Number</td><td>' + delivery[6] + '</td></tr>';
            rows += '<tr><td>Note</td><td>' + delivery[9] + '</td></tr>';

            newBody.append(rows);
            $("table#delivery-content").append(newBody);

            $.get('/delivery/product_delivery/' + delivery[4], function(data) {
                $(".product-delivery-list tbody").remove();
                var code = data[0];
                var description = data[1];
                var batch_no = data[2];
                var expired_date = data[3];
                var qty = data[4];
                var newBody = $("<tbody>");
                $.each(code, function(index) {
                    var newRow = $("<tr>");
                    var cols = '';
                    cols += '<td><strong>' + (index + 1) + '</strong></td>';
                    cols += '<td>' + code[index] + '</td>';
                    cols += '<td>' + description[index] + '</td>';
                    cols += '<td>' + batch_no[index] + '</td>';
                    cols += '<td>' + expired_date[index] + '</td>';
                    cols += '<td>' + qty[index] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                });
                $("table.product-delivery-list").append(newBody);
            });

            var htmlfooter = '<div class="col-md-4 form-group"><p>Recieved Phone: ' + delivery[13] + '</p></div>';
            htmlfooter += '<div class="col-md-4 form-group"><p>Recieved By: ' + delivery[12]   + '</p></div>';
            htmlfooter += '<div class="col-md-4 form-group"><p>Delivered By: ' + delivery[11] + '</p></div>';
            htmlfooter += '<br><br>';
            htmlfooter +=
                '<div class="col-md-2 offset-md-5"><img style="max-width:850px;height:100%;max-height:130px" src="data:image/png;base64,' +
                barcode + '" alt="barcode" /></div>';

            $('#delivery-footer').html(htmlfooter);
            $('#delivery-details').modal('show');
        }

        $(document).ready(function() {
            $(document).on('click', '.open-EditCategoryDialog', function() {
                var url = "delivery/"
                var id = $(this).data('id').toString();
                url = url.concat(id).concat("/edit");

                $.get(url, function(data) {
                    $('#dr').text(data[0]);
                    $('#sr').text(data[1]);
                    $('select[name="status"]').val(data[2]);
                    $('.selectpicker').selectpicker('refresh');
                    $('input[name="delivered_by"]').val(data[3]);
                    $('input[name="recieved_by"]').val(data[4]);
                    $('#customer').text(data[5]);
                    $('textarea[name="address"]').val(data[6]);
                    $('textarea[name="note"]').val(data[7]);
                    $('select[name="courier_id"]').val(data[8]);
                    $('input[name="reference_no"]').val(data[0]);
                    $('input[name="delivery_id"]').val(id);
                    $('.selectpicker').selectpicker('refresh');
                });
                $("#edit-delivery").modal('show');
            });
        });


        $(document).ready(function() {
            $('#delivery-table').DataTable({

            });
        });

    </script>
    <style>
        #delivery-table_paginate {
    margin-left: 500px;

}

        </style>
@endpush
