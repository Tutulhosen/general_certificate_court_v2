@extends('layouts.default')

@section('content')
<div class="row justify-content-center mt-5 mb-10 px-8 mb-lg-15 px-lg-10">
    <div class="col-xl-12 col-xxl-7">
        <!--begin::Form Wizard-->
        <form id="" action="" class="form" method="POST">
            @csrf
            
            
            <div class="" style=" text-align:center; font-weight:bold; color:black">
                <div class="">
                    <h1 >আবেদন বাতিল </h1>
                </div>
            </div>
            <fieldset class="pb-5 create_cause" data-wizard-type="step-content-2"
                data-wizard-state="current">
              
                
                <?php 
                    $order="জনাব ($list->applicent_name), 
আপনার সার্টিফিকেট কপির আবেদনটি ......... কারণে বাতিল করা হল ।";
                ?>

                <input type="hidden" id="appeal_id" name="appeal_id" value="{{$list->appeal_id}}">
                <input type="hidden" id="application_id" name="application_id" value="{{$list->certify_id}}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><span style="color:#FF0000">* </span>বিবরণ</label>
                            <textarea name="description" id="description" rows="10" class="form-control element-block blank"
                                aria-describedby="note-error" aria-invalid="false">{{$order}}</textarea>
                        </div>
                        
                    </div>
                    
                </div>
            

                {{-- @include('courtFeeAppeal') --}}
            </fieldset>
            <br>
            <div class="text-right">
                <button type="button" class="btn btn-success font-weight-bolder text-uppercase px-9 py-4 next_btn" id="next_btn" >প্রেরণ করুন</button>
            </div>
        </form>
        <!--end::Form Wizard-->
    </div>
</div>
@endsection

@section('scripts')
<script>
        
         $(document).on('click', '#next_btn', function(){

             let appeal_id = $('#appeal_id').val();
             let certify_id = $('#application_id').val();
             let description = $('#description').val();
        
            // SweetAlert confirmation
            Swal.fire({
               
                text: "আবেদনটি বাতিল করতে চান ? ",
                
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'হ্যাঁ ',
                cancelButtonText: 'না '
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with the AJAX request if confirmed
                   
                    $.ajax({
                        url: "{{route('appeal.cancel.certify.copy.by.rrdc')}}",
                        type: "POST",
                        data:{
                            appeal_id:appeal_id,
                            certify_id:certify_id,
                            description:description,
                        },
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
    </script>    
@endsection