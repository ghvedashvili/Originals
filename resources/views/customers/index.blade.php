@extends('layouts.master')


@section('top')
    <!-- DataTables --><!-- Log on to codeastro.com for more projects! -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    {{--<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css">--}}

@endsection

@section('content')
    <div class="box box-success">

        <div class="box-header">
            <h3 class="box-title">List of Customers</h3>
        </div>

        <div class="box-header">
            <a onclick="addForm()" class="btn btn-success" ><i class="fa fa-plus"></i> Add Customers</a>
            <a href="{{ route('exportPDF.customersAll') }}" class="btn btn-danger"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
            <a href="{{ route('exportExcel.customersAll') }}" class="btn btn-primary"><i class="fa fa-file-excel-o"></i> Export Excel</a>
        </div>


        <!-- /.box-header -->
        <div class="box-body">
            <table id="customer-table" class="table table-bordered table-hover table-striped">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!-- /.box-body -->
    </div>

    @include('customers.form_import')

    @include('customers.form')

@endsection

@section('bot')

    <!-- DataTables -->
    <script src=" {{ asset('assets/bower_components/datatables.net/js/jquery.dataTables.min.js') }} "></script>
    <script src="{{ asset('assets/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }} "></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    {{-- Validator --}}
    <script src="{{ asset('assets/validator/validator.min.js') }}"></script>

    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>--}}

    {{--<script>--}}
    {{--$(function () {--}}
    {{--$('#items-table').DataTable()--}}
    {{--$('#example2').DataTable({--}}
    {{--'paging'      : true,--}}
    {{--'lengthChange': false,--}}
    {{--'searching'   : false,--}}
    {{--'ordering'    : true,--}}
    {{--'info'        : true,--}}
    {{--'autoWidth'   : false--}}
    {{--})--}}
    {{--})--}}
    {{--</script>--}}

    <script type="text/javascript">
    var table = $('#customer-table').DataTable({
        processing: true,
        serverSide: false, // Yajra-ს გარეშე დააყენე false
        ajax: "{{ route('api.customers') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'}, // შეიცვალა nama -> name
            {data: 'address', name: 'address'}, // შეიცვალა alamat -> address
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'}, // შეიცვალა telepon -> phone
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    function addForm() {
        save_method = "add";
        $('input[name=_method]').val('POST');
        $('#modal-form').modal('show');
        $('#modal-form form')[0].reset();
        $('.modal-title').text('Add Customers');
    }

 function editForm(id) {
    save_method = 'edit';
    $('input[name=_method]').val('PATCH');
    $('#modal-form form')[0].reset();
    
    $.ajax({
        url: "{{ url('customers') }}" + '/' + id + "/edit",
        type: "GET",
        dataType: "JSON",
        success: function(data) {
            $('#modal-form').modal('show');
            $('.modal-title').text('Edit Customer');

            $('#id').val(data.id);
            $('#name').val(data.name);
            $('#address').val(data.address);
            $('#email').val(data.email);
            
            // სახელების შესაბამისობა ფორმასთან:
            $('#city_id').val(data.city_id);
            $('#tel').val(data.tel);   // ფორმაში გაქვს id="tel"
            $('#alternative_tel').val(data.alternative_tel); // ფორმაში გაქვს id="alternative_tel"
            $('#comment').val(data.comment); // ფორმაში გაქვს id="comment"
        },
        error: function() {
            swal("Error", "მონაცემების წამოღება ვერ მოხერხდა", "error");
        }
    });
}

    function deleteData(id){
        var csrf_token = $('meta[name="csrf-token"]').attr('content');
        swal({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url : "{{ url('customers') }}" + '/' + id,
                    type : "POST",
                    data : {'_method' : 'DELETE', '_token' : csrf_token},
                    success : function(data) {
                        table.ajax.reload();
                        swal("Success!", data.message, "success");
                    },
                    error : function () {
                        swal("Error", "Something went wrong", "error");
                    }
                });
            }
        });
    }

   $(function(){
    $('#modal-form form').validator().on('submit', function (e) {
        if (!e.isDefaultPrevented()){
            var id = $('#id').val();
            var url = (save_method == 'add') ? "{{ url('customers') }}" : "{{ url('customers') }}/" + id;

            $.ajax({
                url : url,
                type : "POST",
                data: new FormData($("#modal-form form")[0]),
                contentType: false,
                processData: false,
                success : function(data) {
                    $('#modal-form').modal('hide');
                    table.ajax.reload();
                    swal("Success!", data.message, "success");
                },
                error : function(data){
                    // 1. ვიღებთ შეცდომებს JSON ფორმატში
                    var response = data.responseJSON;
                    var errorString = '';

                    // 2. თუ არის ვალიდაციის შეცდომები (მაგ: email unique)
                    if (response && response.errors) {
                        $.each(response.errors, function (key, value) {
                            errorString += value + '\n'; // ვაგროვებთ ყველა შეცდომას
                        });
                    } else if (response && response.message) {
                        errorString = response.message; // ზოგადი შეცდომა
                    } else {
                        errorString = "Something went wrong. Please try again.";
                    }

                    // 3. გამოგვაქვს SweetAlert-ში
                    swal("Oops!", errorString, "error");
                }
            });
            return false;
        }
    });
});
</script>

@endsection
