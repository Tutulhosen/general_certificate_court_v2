@extends('layouts.default')

@section('content')
    <!--begin::Card-->
    <div class="card card-custom col-12">
        <div class="card-header flex-wrap py-5">
            <div class="card-title">
                <h3 class="card-label"> {{ $page_title }} </h3>
            </div>
            <!-- <div class="card-toolbar">
             <a href="{{ url('division') }}" class="btn btn-sm btn-primary font-weight-bolder">
                <i class="la la-list"></i> ব্যবহারকারীর তালিকা
             </a>
          </div> -->
        </div>
        
      <a href=""></a>
      @if ($errors->any())
      <div class="alert alert-danger">
          <ul>
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
     @endif

        <form action="{{ route('post.organization.store') }}" method="POST">
            @csrf
                

            <div class="card-body">
                @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        <p>{{ $message }}</p>
                    </div>
                @endif
                <div class="mb-12">
                    <div class="form-group row">
                        <div class="col-lg-3 mb-5">
                            <label>বিভাগ <span class="text-danger">*</span></label>
                            <select class="form-control" aria-label=".form-select-lg example" name="division_id"
                                id="division_id" required>
                                    <option value="{{ $division_id }}" selected>{{ $div_name_bn }}
                                    </option>
                               
                            </select>
                        </div>
                        <div class="col-lg-3 mb-5">
                            <label>জেলা <span class="text-danger">*</span></label>
                            <select class="form-control" aria-label=".form-select-lg example" name="district_id"
                                id="district_id" required>
                                <option value="{{ $district_id }}" selected>{{ $dis_name_bn }}
                                </option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-5">
                            <label>উপজেলা নির্বাচন করুন <span class="text-danger">*</span></label>
                            <select class="form-control" aria-label=".form-select-lg example" name="upazila_id"
                                id="upazila_id" required>
                                <option value="">উপজেলা নির্বাচন করুন </option>
                                @foreach ($upazila_list as $single_upazila)
                                    <option value="{{ $single_upazila->id }}">{{ $single_upazila->upazila_name_bn }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-12" style="margin-top:20px;">
                            <label>প্রতিষ্ঠানের নাম <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="office_name_bn" value="{{ old('office_name_bn') }}" required>
                            @error('office_name_bn')
                                    <div class="alert alert-danger"> প্রতিষ্ঠানের নাম বাংলাতে দিন</div>
                                @enderror
                        </div>
                        <div class="col-lg-12" style="margin-top:20px;">
                            <label> প্রতিষ্ঠানের নাম ( ইংরেজি ) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control only_english" name="office_name_en" value="{{ old('office_name_en') }}" required>
                            @error('office_name_en')
                            <div class="alert alert-danger"> প্রতিষ্ঠানের নাম ইংরেজিতে দিন</div>
                        @enderror
                        </div>
                        <div class="col-lg-6" style="margin-top:20px;">
                            <label>প্রতিষ্ঠানের ধরণ <span class="text-danger">*</span></label>
                            <select class="form-control" aria-label=".form-select-lg example" name="organization_type"
                                    id="organization_type" required>
                            
                                    <option value=" ">প্রতিষ্ঠানের ধরন নির্বাচন করুন </option>
                                    <option value="BANK">ব্যাংক</option>
                                    <option value="GOVERNMENT" >সরকারি প্রতিষ্ঠান</option>
                                    <option value="OTHER_COMPANY" >স্বায়ত্তশাসিত প্রতিষ্ঠান</option>
                                </select>
                        </div>
                        <div class="col-lg-6" style="margin-top:20px;">
                            <label>কেন্দ্রীয় শাখা <span class="text-danger">*</span></label>
                            <select class="form-control" aria-label=".form-select-lg example" name="central_branch"
                                    id="central_branch" required>
                            </select>
                        </div>
                        <div class="col-lg-12" style="margin-top:20px;">
                            <label>প্রতিষ্ঠানের ঠিকানা <span class="text-danger">*</span></label>
                            <textarea name="organization_physical_address" class="form-control" id="" cols="30" rows="10" required>{{ old('organization_physical_address') }}</textarea>
                            @error('organization_physical_address')
                                    <div class="alert alert-danger">প্রতিষ্ঠানের ঠিকানা দিন</div>
                                @enderror
                            
                        </div>
                        <div class="col-lg-12" style="margin-top:20px;">
                            <label>প্রাতিষ্ঠানের আইডি (রাউটিং নং ) ইংরেজিতে <span class="text-danger">*</span></label>
                            <input type="text" class="form-control only_english" name="organization_routing_id" value="{{ old('organization_routing_id') }}" required>
                            @error('organization_routing_id')
                            <div class="alert alert-danger"> রাউটিং নং ইংরেজিতে দিন</div>
                         @enderror
                        </div>
                    </div>

                </div>
             
            </div>
    </div>

    <div class="card-footer">
        <div class="row">
            <div class="col-lg-12">
                <button type="submit" class="btn btn-primary font-weight-bold mr-2">সংরক্ষণ</button>
            </div>
        </div>
    </div>

    </form>
    </div>
    <!--end::Card-->
@endsection

{{-- Includable CSS Related Page --}}
@section('styles')
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Page Vendors Styles-->
@endsection

{{-- Scripts Section Related Page --}}

@section('scripts')
    <script src="assets/js/pages/custom/login/login-3.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            
            $(".only_english").keypress(function(event){
                    var ew = event.which;
                    if(ew == 32)
                        return true;
                    if(48 <= ew && ew <= 57)
                        return true;
                    if(65 <= ew && ew <= 90)
                        return true;
                    if(97 <= ew && ew <= 122)
                        return true;
                    return false;
            });

            $('#organization_type').on('change', function(){
                let organization_type=$(this).val();
                let csrf = '{{ csrf_token() }}';
                $.ajax({
                    url: '{{ route('get.central.branch') }}',
                    method: 'post',
                    data: JSON.stringify({
                        organization_type: organization_type,
                        _token: csrf
                    }),
                    cache: false,
                    contentType: 'application/json',
                    processData: false,
                    dataType: 'json',

                    success: function(response) {
                        if (response.status == 'success') {
                       
                            $('#central_branch').html(response.central_branch_html);

                            

                        }
                    }



                });
            })

        });
    </script>
@endsection