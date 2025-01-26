@extends('layouts.default')
@section('content')

    <style>
        .blink {
            animation: blinker 1.5s linear infinite;
            color: red;
            font-family: sans-serif;
        }

        @keyframes blinker {
            50% {
                opacity: 0;
            }
        }
    </style>
    <!--begin::Card-->


<div class="card card-custom">
    <div class="card-header flex-wrap py-5">
        <div class="card-title" style="display: flex; justify-content: space-between; width: 100%">
            <h3 class="card-title h2 font-weight-bolder">{{ $page_title }}</h3>
            <div>
                <a href="javascript:generateAppealPDF()" class="btn btn-danger btn-link">Export</a>
            </div>
        </div>
        @if (Request::is('appeal/trial_date_list'))
            @if (Auth::user()->role_id == 27 ||
                    Auth::user()->role_id == 6 ||
                    Auth::user()->role_id == 34 ||
                    Auth::user()->role_id == 25)
                <div class="card-toolbar">
                    <a href="{{ route('appeal.hearingTimeUpdate') }}" class="btn btn-sm btn-primary font-weight-bolder">
                        <i class="la la-edit"></i>শুনানির সময় পরিবর্তন
                    </a>
                </div>
            @endif
        @endif

        @if (Auth::user()->role_id == 5)
            <div class="card-toolbar">
                <a href="{{ url('case/add') }}" class="btn btn-sm btn-primary font-weight-bolder">
                    <i class="la la-plus"></i>নতুন মামলা এন্ট্রি
                </a>
            </div>
        @endif
    </div>
    <div class="card-body overflow-auto " id="element-to-print-list">
        <div id="card-title-print" class="py-5" style="display: none">
            <h3 class="card-title-print h2 font-weight-bolder">{{ $page_title }}</h3>
        </div>
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                {{ $message }}
            </div>
        @endif

        @include('appeal.search')
        @php
            $today = date('Y-m-d', strtotime(now()));
            $today_time = date('H:i:s', strtotime(now()));
        @endphp
        <table class="table table-hover mb-6 font-size-h5">
            <thead class="thead-customStyle2 font-size-h6">
                <tr style="text-align: justify">
                    <th scope="col">ক্রমিক নং</th>
                    {{-- <th scope="col">ক্রমিক নং</th> --}}
                    <th scope="col" style="">সার্টিফিকেট অবস্থা</th>
                    <th scope="col">মামলা নম্বর</th>
                    @if ($userRole == 34)
                        <th scope="col">জেলা</th>
                    @elseif($userRole == 6)
                        <th scope="col">উপজেলা</th>
                    @endif
                    <th scope="col">ম্যানুয়াল মামলা নম্বর</th>
                    <th scope="col">আবেদনকারীর নাম</th>
                    <th scope="col">জেনারেল সার্টিফিকেট আদালত</th>
                    <th scope="col">পরবর্তী তারিখ</th>
                    <th scope="col">পদক্ষেপ</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($results as $key => $row)
                    <tr>
                        <td scope="row" class="tg-bn">{{ en2bn($key + $results->firstItem()) }}.</td>
                        <td> {{ appeal_status_bng($row->appeal_status) }}</td> {{-- Helper Function for Bangla Status --}}
                        <td>
                            @if (isset($row->case_entry_type))
                                {{-- @dd($row->case_entry_type) --}}
                                @if ($row->case_entry_type == 'RUNNING')
                                    {{ en2bn($row->case_no) }} (ম্যানুয়াল মামলা)
                                @else
                                    {{ en2bn($row->case_no) }} (সিস্টেম এর মামলা)
                                @endif

                                {{-- {{ en2bn($row->case_no) }} --}}
                            @else
                                {{ en2bn($row->case_no) }}
                            @endif
                        </td>
                        <td>{{ $row->manual_case_no }}</td>
                        @if ($userRole == 34)
                            <td>{{ isset($row->district->district_name_bn) ? $row->district->district_name_bn : ' ' }}
                            </td>
                        @elseif($userRole == 6)
                            <td>{{ isset($row->upazila->upazila_name_bn) ? $row->upazila->upazila_name_bn : ' ' }}</td>
                        @endif
                        @if ($row->is_applied_for_review == 0)
                            <td>
                                {{-- @dd($row->id); --}}
                                @php
                                    $applicant_name = DB::table('gcc_appeal_citizens')
                                        ->join('gcc_citizens', 'gcc_appeal_citizens.citizen_id', 'gcc_citizens.id')
                                        ->where('gcc_appeal_citizens.appeal_id', $row->id)
                                        ->where('gcc_appeal_citizens.citizen_type_id', 1)
                                        ->select('gcc_citizens.citizen_name')
                                        ->first();
                                @endphp
                                {{ $applicant_name->citizen_name ?? '' }}
                            </td>
                        @else
                            <td>{{ $row->reviewerName->name }}</td>
                        @endif
                        <td>@php
                            if (isset($row->court_id)) {
                                echo DB::table('court')
                                    ->where('id', $row->court_id)
                                    ->first()->court_name;
                            }
                        @endphp</td>
                        <td>{{ $row->next_date == '1970-01-01' ? '-' : en2bn($row->next_date) }}</td>

                        <td>
                            <div class="btn-group float-right">
                                <button class="btn btn-primary font-weight-bold btn-sm dropdown-toggle" type="button"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">পদক্ষেপ</button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item"
                                        href="{{ route('appeal.appealView', encrypt($row->id)) }}">বিস্তারিত তথ্য</a>
                                    @if ($userRole != 35)
                                        @if ($row->appeal_status != 'SEND_TO_ASST_GCO' && $row->appeal_status != 'SEND_TO_GCO' )
                                            @if ($userRole == 6 || $userRole == 11 || $userRole == 12 || $userRole == 9)
                                                
                                                @if ($row->appeal_process_status == 'DM_FOR_CERTIFICATE_COPY')
                                                    <button type="button" class="dropdown-item" id="sent_to_rrdc" data-id="{{$row->id}}">রেকর্ডরুম ডেপুটি কালেক্টর বরাবর প্রেরণ</button>
                                                @elseif ($row->appeal_process_status == 'SENT_TO_RRDC')
                                                    <button type="button" class="dropdown-item" id="sent_to_rk" data-id="{{$row->id}}">রেকর্ড কিপার বরাবর প্রেরণ</button>
                                                @elseif ($row->appeal_process_status == 'SENT_TO_RK')
                                                    @if ($row->appeal_process_fee_status == 'CERTIFY_COPY_FEE_COMPLETE')
                                                        <button type="button" class="dropdown-item" id="sent_certificate_copy" data-id="{{$row->id}}">সার্টিফিকেট কপি প্রেরণ</button>
                                                        <a class="dropdown-item"href="{{ route('appeal.generateShortOrderTemplatePDF', encrypt($row->id)) }}" target="_blank" >নথি গণনা ও প্রিন্ট</a>
                                                    @elseif($row->appeal_process_fee_status != 'PROCESS_COMPLETE')
                                                        <a class="dropdown-item"href="{{ route('appeal.generateShortOrderTemplatePDF', encrypt($row->id)) }}" target="_blank" >নথি গণনা ও প্রিন্ট</a>
                                                        <a class="dropdown-item" href="{{ route('appeal.fee.for.nothi', encrypt($row->id)) }}">ফি আদায় </a>
                                                    @endif
                                                @endif
                                                @if ($row->appeal_status == 'SENT_TO_ASST_DM')
                                                    @if ($userRole == 6)
                                                        <a class="dropdown-item" href="#">সহকারীর অপেক্ষায়</a>
                                                    @endif
                                                    @if ($userRole == 9)
                                                        <a class="dropdown-item" href="{{ route('appeal.edit', encrypt($row->id)) }}">সংশোধন ও প্রেরণ</a>
                                                    @endif
                                                @endif
                                                @if ($row->appeal_status == 'SENT_TO_DM')
                                                    @if ($userRole == 9)
                                                        <a class="dropdown-item" href="#">ডিএম এর আদেশের অপেক্ষায়</a>
                                                    @endif
                                                    @if ($userRole == 6)
                                                        <a class="dropdown-item" href="{{ route('appeal.edit', encrypt($row->id)) }}">সংশোধন ও প্রেরণ</a>
                                                        <button type="button" class="dropdown-item" id="sent_to_adm" data-id="{{$row->id}}">এডিএম বরাবর প্রেরণ</button>
                                                    @endif
                                                @endif
                                            @else
                                                @if (globalUserInfo()->role_id==27 || globalUserInfo()->role_id==28)
                                                    
                                                    <a class="dropdown-item"href="{{ route('appeal.nothiView', encrypt($row->id)) }}">নথি দেখুন</a>
                                                    @if ($row->is_required_for_nothi == 1)
                                                    <button type="button" class="dropdown-item" id="sent_nothi_to_adm" data-id="{{$row->id}}">নথি প্রেরণ করুন</button>
                                                    @endif
                                                @endif
                                                @if (globalUserInfo()->role_id==6 || globalUserInfo()->role_id==7 || globalUserInfo()->role_id==9 || globalUserInfo()->role_id==10)
                                                    @if ($row->is_required_for_nothi == 2)
                                                        <a class="dropdown-item"href="{{ route('appeal.nothiView', encrypt($row->id)) }}">নথি দেখুন</a>
                                                    @endif
                                                @endif
                                            @endif
                                        @endif
                                        @if (
                                            $row->next_date == $today &&
                                                $row->next_date_trial_time <= $today_time &&
                                                $row->appeal_status != 'CLOSED' &&
                                                $row->is_hearing_required == 1)
                                            <a class="dropdown-item blink"
                                                href="{{ route('jitsi.meet', ['appeal_id' => encrypt($row->id)]) }}"
                                                style="color: red;" target="_blank">অনলাইন শুনানি</a>
                                        @endif

                                        @if ($userRole == 28)
                                            @if ($row->action_required == 'GCO' && $row->appeal_status != 'CLOSED')
                                                <a class="dropdown-item" href="#">জিসিও এর আদেশের অপেক্ষায়</a>
                                            @else
                                                @if ($row->appeal_status == 'SEND_TO_ASST_GCO')
                                                    <a class="dropdown-item"
                                                        href="{{ route('appeal.edit', encrypt($row->id)) }}">সংশোধন ও
                                                        প্রেরণ</a>
                                                @elseif($row->action_required == 'ASST' && $row->appeal_status != 'CLOSED')
                                                    <a class="dropdown-item"
                                                        href="{{ route('appeal.edit', encrypt($row->id)) }}">সংশোধন ও
                                                        প্রেরণ</a>
                                                @endif
                                            @endif
                                        @endif
                                        @if ($userRole == 10)
                                            @if ($row->appeal_status == 'SENT_TO_ADM')
                                                <a class="dropdown-item" href="#">এডিএম এর আদেশের অপেক্ষায়</a>
                                            @endif
                                            @if ($row->appeal_status == 'SENT_TO_ASST_ADM')
                                                
                                                <a class="dropdown-item" href="{{ route('appeal.edit', encrypt($row->id)) }}">সংশোধন ও প্রেরণ</a>
                                            @endif

                                        @endif
                                        @if ($userRole == 7)
                                            @if ($row->appeal_status == 'SENT_TO_ADM')
                                                <a class="dropdown-item" href="{{ route('appeal.trial', encrypt($row->id)) }}">সংশোধন ও প্রেরণ</a>
                                            @endif
                                            @if ($row->appeal_status == 'SENT_TO_ASST_ADM')
                                                <a class="dropdown-item" href="#">সহকারীর অপেক্ষায়</a>
                                            @endif

                                        @endif
                                        @if ($row->action_required == 'ASST' && $row->appeal_status != 'CLOSED' && $userRole != 28)
                                            <a class="dropdown-item" href="#">সহকারীর অপেক্ষায়</a>
                                        @else
                                            @if (
                                                    $userRole == 25 ||
                                                    $userRole == 27 ||
                                                    $userRole == 34)
                                                @if ($row->appeal_status == 'SEND_TO_GCO')
                                                    {{-- <a class="dropdown-item" href="{{ route('appeal.status_change', encrypt($row->id)) }}?status=REJECTED">মামলা বর্জন  করুন</a> --}}
                                                    <a class="dropdown-item"
                                                        href="{{ route('appeal.trial', encrypt($row->id)) }}">সংশোধন ও প্রেরণ</a>
                                                @elseif (
                                                    $row->appeal_status == 'SEND_TO_DC' ||
                                                        $row->appeal_status == 'SEND_TO_DIV_COM' ||
                                                        $row->appeal_status == 'SEND_TO_LAB_CM')
                                                    @if ($userRole == 25 || $userRole == 34)
                                                        <a class="dropdown-item"
                                                            href="{{ route('appeal.trial', encrypt($row->id)) }}">মামলা
                                                            গ্রহণ করুন</a>
                                                    @endif
                                                @elseif (
                                                    $row->appeal_status == 'ON_TRIAL' ||
                                                        $row->appeal_status == 'ON_TRIAL_DC' ||
                                                        $row->appeal_status == 'ON_TRIAL_DIV_COM' ||
                                                        $row->appeal_status == 'ON_TRIAL_LAB_CM')
                                                    <a class="dropdown-item"
                                                        href="{{ route('appeal.trial', encrypt($row->id)) }}">কার্যক্রম
                                                        পরিচালনা করুন</a>
                                                @endif
                                                @if (Request::url() === route('appeal.collectPaymentList'))
                                                    <a class="dropdown-item"
                                                        href="{{ route('appeal.collectPayment', encrypt($row->id)) }}">অর্থ
                                                        আদায়</a>
                                                @endif
                                            @endif
                                           
                                        @endif
                                    @else
                                        @if ($row->appeal_status == 'DRAFT')
                                            <a class="dropdown-item"
                                                href="{{ route('appeal.edit', encrypt($row->id)) }}">সংশোধন করুন</a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {!! $results->links() !!}
        </div>
    </div>
    <!--end::Card-->

@endsection

{{-- Includable CSS Related Page --}}
@section('styles')
    <!-- <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" /> -->
    <!--end::Page Vendors Styles-->
@endsection


@section('scripts')
   <script src="{{ asset('js/pages/crud/forms/widgets/bootstrap-datepicker.js') }}"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
         integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
         crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <script>
        function generateAndHidePDF() {
            // Show the card-title-print element temporarily for PDF generation
            document.getElementById('card-title-print').style.display = 'block';

            // Generate PDF
            generateAppealPDF();
        }

        function generateAppealPDF() {
            var element = document.getElementById('element-to-print-list');
            const elementtoprintlist = document.getElementById('card-title-print');

            var opt = {
                margin: 1,
                filename: 'myfile.pdf',
                pagebreak: {
                    avoid: ['tr', 'td']
                },
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 10
                },
            };

            // New Promise-based usage:
            html2pdf().set(opt).from(element).save().then(() => {
                // Hide the card-title-print element after PDF generation is complete
                document.getElementById('card-title-print').style.display = 'none';
            });
        }

        $(document).on('click', '#sent_to_rrdc', function(){
            let appeal_id = $(this).data('id');
            
            // SweetAlert confirmation
            Swal.fire({
               
                text: "জনাব (রেকর্ডরুম ডেপুটি কালেক্টর), এই মামলার সার্টিফিকেট কপির প্রক্রিয়া করনের পরবর্তী পদক্ষেপ নেওয়ার জন্য আপনাকে নির্দেশ প্রদান করা হল ।",
                
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'প্রেরণ করুন',
                cancelButtonText: 'বাতিল করুন'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with the AJAX request if confirmed
                   
                    $.ajax({
                        url: '/appeal/sent/to/deputy/collector/' + appeal_id,
                        type: "GET",
                        dataType: "json",
                        success: function(response) {
                            Swal.fire(
                                'নির্দেশনাটি সফলভাবে প্রেরণ হয়েছে ।'
                            ).then(() => {
                                // Redirect to the dashboard route after confirmation
                                window.location.href = '{{ route("dashboard") }}';
                            });
                        },
                        error: function(xhr, status, error) {
                            // Optional: Handle the error
                            Swal.fire(
                                'নির্দেশনাটি প্রেরণ হয়নি। আবার চেষ্টা করুন ।'
                            );
                        }
                    });
                }
            });
        });

        $(document).on('click', '#sent_to_rk', function(){
            let appeal_id = $(this).data('id');
            
            // SweetAlert confirmation
            Swal.fire({
               
                text: "জনাব (রেকর্ড কিপার), এই মামলার সার্টিফিকেট কপির প্রক্রিয়া করনের পরবর্তী পদক্ষেপ নেওয়ার জন্য আপনাকে নির্দেশ প্রদান করা হল ।",
                
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'প্রেরণ করুন',
                cancelButtonText: 'বাতিল করুন'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with the AJAX request if confirmed
                   
                    $.ajax({
                        url: '/appeal/sent/to/deputy/collector/' + appeal_id,
                        type: "GET",
                        dataType: "json",
                        success: function(response) {
                            Swal.fire(
                                'নির্দেশনাটি সফলভাবে প্রেরণ হয়েছে ।'
                            ).then(() => {
                                // Redirect to the dashboard route after confirmation
                                window.location.href = '{{ route("dashboard") }}';
                            });
                        },
                        error: function(xhr, status, error) {
                            // Optional: Handle the error
                            Swal.fire(
                                'নির্দেশনাটি প্রেরণ হয়নি। আবার চেষ্টা করুন ।'
                            );
                        }
                    });
                }
            });
        });

        $(document).on('click', '#sent_certificate_copy', function(){
            let appeal_id = $(this).data('id');
            
            // SweetAlert confirmation
            Swal.fire({
               
                text: "জনাব (রেকর্ড কিপার), আবেদনকারি সার্টিফিকেট কপির সকল ফি প্রদান করেছেন। সুতরাং  এই মামলার নথি প্রিন্ট করে RRDC স্বাক্ষরসহ আবেদনকারিকে ডাকযোগে প্রেরণ করার জন্য বলা হল ।",
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'ঠিকআছে',
               
            })
          
        });

        $(document).on('click', '#sent_to_adm', function(){
            let appeal_id = $(this).data('id');
            
            // SweetAlert confirmation
            Swal.fire({
               
                text: "জনাব (এডিএম), এই মামলার আপিল পরবর্তী কার্যক্রম পরিচালনা করার জন্য আপনাকে বলা হল ।",
                
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'প্রেরণ করুন',
                cancelButtonText: 'বাতিল করুন'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with the AJAX request if confirmed
                   
                    $.ajax({
                        url: '/appeal/sent/to/adm/' + appeal_id,
                        type: "GET",
                        dataType: "json",
                        success: function(response) {
                            Swal.fire(
                                'নির্দেশনাটি সফলভাবে প্রেরণ হয়েছে ।'
                            ).then(() => {
                                // Redirect to the dashboard route after confirmation
                                window.location.href = '{{ route("dashboard") }}';
                            });
                        },
                        error: function(xhr, status, error) {
                            // Optional: Handle the error
                            Swal.fire(
                                'নির্দেশনাটি প্রেরণ হয়নি। আবার চেষ্টা করুন ।'
                            );
                        }
                    });
                }
            });
        });

        $(document).on('click', '#sent_nothi_to_adm', function(){
            let appeal_id = $(this).data('id');
            
            // SweetAlert confirmation
            Swal.fire({
               
                text: "এই মামলার আপিল পরবর্তী কার্যক্রম পরিচালনা করার জন্য সকল নথি প্রেরণ করা হল ।",
                
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'প্রেরণ করুন',
                cancelButtonText: 'বাতিল করুন'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with the AJAX request if confirmed
                   
                    $.ajax({
                        url: '/appeal/sent/nothi/to/adm/' + appeal_id,
                        type: "GET",
                        dataType: "json",
                        success: function(response) {
                            Swal.fire(
                                ' সফলভাবে প্রেরণ হয়েছে ।'
                            ).then(() => {
                                // Redirect to the dashboard route after confirmation
                                window.location.href = '{{ route("dashboard") }}';
                            });
                        },
                        error: function(xhr, status, error) {
                            // Optional: Handle the error
                            Swal.fire(
                                ' প্রেরণ হয়নি। আবার চেষ্টা করুন ।'
                            );
                        }
                    });
                }
            });
        });


        // common datepicker
        $('.common_datepicker').datepicker({
            format: "dd/mm/yyyy",
            todayHighlight: true,
            orientation: "bottom left"
        });
    </script>
   
   </script>
      <script type="text/javascript">
         jQuery(document).ready(function ()
         {
            // District Dropdown
            jQuery('select[name="division"]').on('change',function(){
               var dataID = jQuery(this).val();
               // var category_id = jQuery('#category_id option:selected').val();
               jQuery("#district_id").after('<div class="loadersmall"></div>');
               // $("#loading").html("<img src='{{ asset('media/preload.gif') }}' />");
               // jQuery('select[name="mouja"]').html('<option><div class="loadersmall"></div></option');
               // jQuery('select[name="mouja"]').attr('disabled', 'disabled');
               // jQuery('.loadersmall').remove();
               if(dataID)
               {
                  jQuery.ajax({
                     url : '{{url("/")}}/case/dropdownlist/getdependentdistrict/' +dataID,
                     type : "GET",
                     dataType : "json",
                     success:function(data)
                     {
                        jQuery('select[name="district"]').html('<div class="loadersmall"></div>');
                        //console.log(data);
                        // jQuery('#mouja_id').removeAttr('disabled');
                        // jQuery('#mouja_id option').remove();

                        jQuery('select[name="district"]').html('<option value="">-- নির্বাচন করুন --</option>');
                        jQuery.each(data, function(key,value){
                           jQuery('select[name="district"]').append('<option value="'+ key +'">'+ value +'</option>');
                        });
                        jQuery('.loadersmall').remove();
                        // $('select[name="mouja"] .overlay').remove();
                        // $("#loading").hide();
                     }
                  });
               }
               else
               {
                  $('select[name="district"]').empty();
               }
            });

            // Upazila Dropdown
            jQuery('select[name="district"]').on('change',function(){
                var dataID = jQuery(this).val();
                // var category_id = jQuery('#category_id option:selected').val();
                jQuery("#upazila_id").after('<div class="loadersmall"></div>');
                // $("#loading").html("<img src='{{ asset('media/preload.gif') }}' />");
                // jQuery('select[name="mouja"]').html('<option><div class="loadersmall"></div></option');
                // jQuery('select[name="mouja"]').attr('disabled', 'disabled');
                // jQuery('.loadersmall').remove();
                /*if(dataID)
                {*/
                jQuery.ajax({
                url : '{{url("/")}}/case/dropdownlist/getdependentupazila/' +dataID,
                type : "GET",
                dataType : "json",
                success:function(data)
                {
                    jQuery('select[name="upazila"]').html('<div class="loadersmall"></div>');
                        //console.log(data);
                        // jQuery('#mouja_id').removeAttr('disabled');
                        // jQuery('#mouja_id option').remove();

                        jQuery('select[name="upazila"]').html('<option value="">-- নির্বাচন করুন --</option>');
                        jQuery.each(data, function(key,value){
                        jQuery('select[name="upazila"]').append('<option value="'+ key +'">'+ value +'</option>');
                    });
                        jQuery('.loadersmall').remove();
                        // $('select[name="mouja"] .overlay').remove();
                        // $("#loading").hide();
                    }
                });
                //}

                // Load Court
                var courtID = jQuery(this).val();
                // var category_id = jQuery('#category_id option:selected').val();
                jQuery("#court_id").after('<div class="loadersmall"></div>');
                
                jQuery.ajax({
                    url : '{{url("/")}}/court/dropdownlist/getdependentcourt/' +courtID,
                    type : "GET",
                    dataType : "json",
                    success:function(data)
                    {
                        jQuery('select[name="court"]').html('<div class="loadersmall"></div>');
                        

                        jQuery('select[name="court"]').html('<option value="">-- নির্বাচন করুন --</option>');
                        jQuery.each(data, function(key,value){
                            jQuery('select[name="court"]').append('<option value="'+ key +'">'+ value +'</option>');
                        });
                        jQuery('.loadersmall').remove();
                        
                    }
                });
                
            });

         
      

      });
   </script>
@endsection
