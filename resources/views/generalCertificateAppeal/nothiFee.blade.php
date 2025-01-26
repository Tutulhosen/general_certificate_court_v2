@extends('layouts.default')

@section('content')
    <div class="container">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header bg-success">
                    <h3 class="card-title h3 font-weight-bolder">{{ $page_title }}</h3>
                    <div class="card-toolbar">
                    </div>
                </div>

                <!-- <div class="loadersmall"></div> -->
                @if ($errors->any())
                <div class="card-body">
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
                
                <!--begin::Form-->
                <form action="" class="form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <fieldset class="mb-8">
                            <div class="from-group row">
                                <input type="hidden" name="appeal_id" id="appeal_id" value="{{$appeal->id}}">
                                <input type="hidden" name="certify_id" id="appeal_id" value="{{$certify_id}}">
                                <input type="hidden" name="citizen_name" id="citizen_name" value="{{$user_info['defaulterCitizen']['citizen_name']}}">
                                <div class="col-lg-4 mb-3">
                                    <label> পেজ সংখ্যা <span class="text-danger">*</span></label>
                                    <input type="number" name="total_page" id="total_page" class="form-control form-control-sm" placeholder="পেজ সংখ্যা ">
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <label> খরচ (প্রতি পেজ) <span class="text-danger">*</span></label>
                                    <input type="number" name="cost_per_page" id="cost_per_page" class="form-control form-control-sm" placeholder="খরচ (প্রতি পেজ) ">
                                </div>

                                <div class="col-lg-4 mb-3">
                                    <label> খরচ (মোট) <span class="text-danger">*</span></label>
                                    <input type="number" name="cost_total" id="cost_total" class="form-control form-control-sm" placeholder="খরচ (মোট)" readonly>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label><span style="color:#FF0000">* </span>বিবরণ</label>
                                        <textarea name="description" id="description" rows="10" class="form-control element-block blank"
                                            aria-describedby="note-error" aria-invalid="false"></textarea>
                                    </div>
                                    
                                </div>
                                
                            </div>
                            <button type="button" class="btn btn-success" id="sent_to_defaulter">প্রেরণ করুন</button>
                        </fieldset>
                    </div> <!--end::Card-body-->

                </form>
                <!--end::Form-->
            </div>
        </div>
    </div>


    
@endsection

@section('scripts')

    <script>
        $(document).ready(function() {
            function updateText() {
                let citizen_name = $('#citizen_name').val();
                let total_page = $('#total_page').val();
                let cost_per_page = $('#cost_per_page').val();
                let cost_total = $('#cost_total').val();
                let text = "জনাব ( " + citizen_name + " ), আপনার সার্টিফিকেট কপির মোট পেজ সংখ্যা " + total_page + "। উক্ত কপির জন্য আপনাকে মোট " + cost_total + " টাকা প্রদান করার জন্য বলা হল ।";
                $('#description').text(text);
            }

            // Call the function when any of the input fields change
            $('#cost_per_page, #total_page, #cost_total').on('input', function() {
                updateText();
            });
        });
    </script>
    {{-- total cost calculate  --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const totalPageInput = document.getElementById('total_page');
            const costPerPageInput = document.getElementById('cost_per_page');
            const costTotalInput = document.getElementById('cost_total');

            function calculateTotalCost() {
                const totalPages = parseFloat(totalPageInput.value) || 0;
                const costPerPage = parseFloat(costPerPageInput.value) || 0;
                const totalCost = totalPages * costPerPage;

                costTotalInput.value = Math.round(totalCost);  
            }

            // Add event listeners to calculate the total whenever the values change
            // totalPageInput.addEventListener('input', calculateTotalCost);
            costPerPageInput.addEventListener('input', calculateTotalCost);
        });
    </script>

    <script>
        
        $(document).on('click', '#sent_to_defaulter', function(){
            let appeal_id = $('#appeal_id').val();
            let citizen_name = $('#citizen_name').val();
            let total_page = $('#total_page').val();
            let cost_total = $('#cost_total').val();
            let description = $('#description').val();
            let certify_id = $('#certify_id').val();
          
            // SweetAlert confirmation
            Swal.fire({
               
                text: "জনাব ( "+citizen_name+" ), আপনার সার্টিফিকেট কপির মোট পেজ সংখ্যা " + total_page+ "। উক্ত কপির জন্য আপনাকে মোট " +cost_total+ " টাকা প্রদান করার জন্য বলা হল ।",
                
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'প্রেরণ করুন',
                cancelButtonText: 'বাতিল করুন'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with the AJAX request if confirmed
                 
                   
                    $.ajax({
                        url: "{{route('appeal.sent.to.defaulter')}}",
                        data:{
                            'appeal_id':appeal_id,
                            'total_page':total_page,
                            'cost_total':cost_total,
                            'description':description,
                            'certify_id':certify_id,
                        },
                        type: "POST",
                        dataType: "json",
                        success: function(response) {
                            Swal.fire(
                                'নির্দেশনাটি সফলভাবে প্রেরণ হয়েছে ।'
                            ).then(() => {
                                // Redirect to the dashboard route after confirmation
                                window.location.href = '{{ route("dashboard") }}';
                                toastr.success('নির্দেশনাটি সফলভাবে প্রেরণ হয়েছে ।', "Success");
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
        })
    </script>

@endsection
