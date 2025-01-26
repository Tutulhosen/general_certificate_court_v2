<fieldset class="mb-8 p-7">
    <legend>কার্যক্রম </legend>
    <div class="panel panel-info" id="appeal_date_time_status_new">

        @include('appealInitiate.inc._cer_asst_initial_comments')

        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="form-label">খুঁজুন (আদেশের ধরন)</label>
                        <input type="text" id="search_short_order_important" class="form-control">
                    </div>
                </div>

            </div>
            <br>
            @if ($user_court_info->role_id == 7)
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group"><label>আদেশের ধরন</label>
                            <div class="form-control form-control-sm" style="height: 253px; overflow-y: scroll;">
                                <label class="radio radio-outline radio-primary mb-3 ">
                                    <input type="radio" class="shortOrderCheckBox" onchange="updateNote(this)"
                                        name="shortOrder[]" id="shortOrder1" value="3020">
                                    <span class="mr-2 case_short_decision_data" data-string=""
                                        data-row_id_index=""></span>
                                    শুনানি
                                </label>

                                <label class="radio radio-outline radio-primary mb-3 ">
                                    <input type="radio" class="shortOrderCheckBox" onchange="updateNote(this)"
                                        name="shortOrder[]" id="shortOrder1" value="3021">
                                    <span class="mr-2 case_short_decision_data" data-string=""
                                        data-row_id_index=""></span>
                                    আবেদন নথিজাত
                                </label>
                                <label class="radio radio-outline radio-primary mb-3 ">
                                    <input type="radio" class="shortOrderCheckBox" onchange="updateNote(this)"
                                        name="shortOrder[]" id="shortOrder1" value="3022">
                                    <span class="mr-2 case_short_decision_data" data-string=""
                                        data-row_id_index=""></span>
                                    নথিকল
                                </label>
                                <label class="radio radio-outline radio-primary mb-3 ">
                                    <input type="radio" class="shortOrderCheckBox" onchange="updateNote(this)"
                                        name="shortOrder[]" id="shortOrder1" value="3023">
                                    <span class="mr-2 case_short_decision_data" data-string=""
                                        data-row_id_index=""></span>
                                    আদেশ -১
                                </label>


                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group"><label for="note">আদেশ</label>
                            <textarea id="note" name="note" rows="10" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group"><label>আদেশের ধরন</label>
                            <div class="form-control form-control-sm" style="height: 253px; overflow-y: scroll;">
                                @forelse ($shortOrderList as $row)
                                    @php
                                        $checked = '';
                                        if (count($notApprovedShortOrderCauseList) > 0) {
                                            foreach ($notApprovedShortOrderCauseList as $key => $value) {
                                                // dd($notApprovedShortOrderCauseList);
                                                if ($value->case_shortdecision_id == $row->id) {
                                                    $checked = 'checked';
                                                }
                                            }
                                        }
                                    @endphp
                                    <label class="radio radio-outline radio-primary mb-3 radio_id_{{ $row->id ?? '' }}">
                                        <input value="{{ $row->id ?? '' }}" type="radio" class="shortOrderCheckBox"
                                            onchange="updateNote(this)" name="shortOrder[]"
                                            id="shortOrder_{{ $row->id ?? '' }}" desc="{{ $row->delails ?? '' }}"
                                            {{ $checked }}>
                                        <span class="mr-2 case_short_decision_data"
                                            data-string="{{ $row->case_short_decision ?? '' }}"
                                            data-row_id_index="{{ $row->id ?? '' }}"></span>
                                        {{ $row->case_short_decision ?? '' }}
                                    </label>
                                @empty
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label for="note">আদেশ</label>
                                <a id="voice_to_text" type="button" class="btn btn-primary" {{-- href="https://translate.google.com/?sl=bn&tl=en&op=translate" 
                                   target="_blank" --}}>
                                    Google Translate Voice to Text লিঙ্ক
                                </a>
                            </div>
                            <div style="display: flex; gap:15px">
                                <div id="note_container" style="flex: 1;">
                                    <textarea id="note" name="note" rows="10" class="form-control mt-2"></textarea>
                                </div>
                                <div class="position-relative" style="display: none; flex: 1;" id="output">
                                    <div>
                                        <i class="fas fa-times close-icon" id="voice_to_text_cross"
                                            style="position: absolute; top: 5px; right: 5px; cursor: pointer; font-size: 16px; color: rgb(255, 12, 12)"></i>
                                        <textarea id="output_text_area" rows="10" class="form-control mt-2"></textarea>
                                    </div>
                                    <div class="d-flex justify-content-center align-items-center mt-2"
                                        style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);">
                                        <div class="button text-center" id="start_recording_div">
                                            <button id="startRecording_one" class="btn btn-light">
                                                <img src="https://cdn-icons-png.flaticon.com/512/25/25682.png"
                                                    alt="Microphone Icon" width="16" height="16">
                                            </button>
                                            {{-- <small>Tap to Record</small> --}}
                                        </div>

                                        <div style="background-color: white; display: none" id="stop_recording_div">
                                            <button id="stopRecording"
                                                class="stop-button text-danger"style="border: none; background-color: white">
                                                <i class="fas fa-stop text-danger"></i> Stop Recording
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    {{-- <div class="button">
                        <button id="startRecording_one"><img src="https://cdn-icons-png.flaticon.com/512/25/25682.png" alt="" width="50" height="50"></button>
                        <small>Tap to Record</small>
                    </div> --}}
                    {{-- <div class="button">
                        <button id="startRecording_one">
                            <img src="https://cdn-icons-png.flaticon.com/512/25/25682.png" alt="Microphone Icon"
                                width="24" height="24">
                        </button>
                        <small>Tap to Record</small>
                    </div> --}}

                    {{-- <div class="buttons">
                        <button id="clearButton">Clear Text</button>
                        <button id="stopRecording">Stop Recording</button>
                    </div> --}}

                </div>

            @endif

            <div class="row" style="padding: 20px 0; display: none" id="manual_short_decision_container">
                <div class="col-md-12">
                    <div class="from-group">
                        <label for="manual_short_decision" class="control-label"><b>মানুয়াল আদেশের ধরন</b></label>
                        <input type="text" name="manual_short_decision" id="manual_short_decision" value=""
                            class="form-control form-control-sm " placeholder="মানুয়াল আদেশ" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group" id="warrantExecutorDetails" style="display: none;">
                        <div class="card card-custom mb-5 shadow">
                            <div class="card-header bg-primary-o-50">
                                <div class="card-title">
                                    <h3 class="card-label" id='warrantExecutorHeading'>ওয়ারেন্ট বাস্তবায়নকারীর তথ্য
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @include('appealTrial.inc._warrentExecutorDetails')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group" id="auctionOrderPossessionDetails" style="display: none;">
                        <div class="card card-custom mb-5 shadow">
                            <div class="card-header bg-primary-o-50">
                                <div class="card-title">

                                    <h3 class="card-label">নিলাম দখল অর্পণের কিছু প্রয়োজনীয় তথ্য</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @include('appealTrial.inc._auction_order_aditional_info')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group" id="29_dhara_additional_info" style="display: none;">
                        <div class="card card-custom mb-5 shadow">
                            <div class="card-header bg-primary-o-50">
                                <div class="card-title">

                                    <h3 class="card-label">২৯ ধারার ( গ্রেফতারী পরোয়ানা ) প্রয়োজনীয় তথ্য</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @include('appealTrial.inc._29_dhara_additional_info')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group" id="metting_link_for_sunani" style="display: none;">
                        <div class="card card-custom mb-5 shadow">
                            <div class="card-header bg-primary-o-50">
                                <div class="card-title">

                                    <h3 class="card-label">মিটিং লিঙ্ক তৈরি করুন</h3>
                                </div>
                            </div>
                           
                            <div class="card-body d-flex align-items-center" style="gap: 15px;">
                                <p class="mb-0" style="font-weight: 600; font-size: 18px">মিটিং লিঙ্ক:</p>
                                <p class="mb-0" style="font-weight: 600; font-size: 18px; color:rgb(0, 102, 255);">https://vc.bcc.gov.bd/</p>
                                <input type="text" class="form-control fs-5" placeholder="Meeting name" name="meeting_name" style="max-width: 250px;">
                                <p class="mb-0" style="font-weight: 600; font-size: 18px">/{{$appeal['case_no']}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group" id="_zill_sent_addtional_info" style="display: none;">
                        <div class="card card-custom mb-5 shadow">
                            <div class="card-header bg-primary-o-50">
                                <div class="card-title">

                                    <h3 class="card-label">দেনাদারকে সিভিল জেলে সোপর্দ প্রয়োজনীয় তথ্য</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @include('appealTrial.inc._zill_sent_addtional_info')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group" id="_seventh_order_addtional" style="display: none;">
                        <div class="card card-custom mb-5 shadow">
                            <div class="card-header bg-primary-o-50">
                                <div class="card-title">
                                    <h3 class="card-label" id="unique">৭ ধারার নোটিশ জারী কিছু প্রয়োজনীয় তথ্য</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @include('appealTrial.inc._7_dahara_aditional_info')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group" id="_10ka_order_addtional" style="display: none;">
                        <div class="card card-custom mb-5 shadow">
                            <div class="card-header bg-primary-o-50">
                                <div class="card-title">

                                    <h3 class="card-label"> ১০(ক) ধারার নোটিশ জারী কিছু প্রয়োজনীয় তথ্য</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @include('appealTrial.inc._10ka_dahara_aditional_info')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="appeal_date_time_status">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="offenderGender" class="control-label"> অবস্থা</label>
                        <select name="status" id="status" class=" form-control form-control-sm">
                            @if ($user_court_info->role_id == 27)
                                <option value="ON_TRIAL">চলমান</option>
                            @elseif($user_court_info->role_id == 6)
                                <option value="ON_TRIAL_DC">চলমান</option>
                            @elseif($user_court_info->role_id == 34)
                                <option value="ON_TRIAL_DIV_COM">চলমান</option>
                            @elseif($user_court_info->role_id == 25)
                                <option value="ON_TRIAL_LAB_CM">চলমান</option>
                            @elseif($user_court_info->role_id == 7)
                                <option value="ON_TRIAL_ADM">চলমান</option>
                            @elseif($user_court_info->role_id == 10)
                                <option value="ON_TRIAL_ADM">চলমান</option>
                            @endif
                            <!-- <option value="2">মুলতবি</option> -->
                            <?php
                            if ($user_court_info->role_id == 7 || $user_court_info->role_id == 10) {
                                $value = 'CLOSED_ADM';
                            } else {
                                $value = 'CLOSED';
                            }
                            ?>
                            <option value="{{ $value }}">নিষ্পতি</option>


                        </select>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="row form-group">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>আদেশের তারিখ</label>

                                <input readonly type="text" name="conductDate" id="conductDate"
                                    value="{{ date('d-m-Y', strtotime(now())) ?? '' }}"
                                    class="form-control form-control-sm " placeholder="দিন/মাস/তারিখ"
                                    autocomplete="off">
                            </div>

                        </div>
                        <div class="col-md-8" id="newnextDatePublish">
                            <div class="row form-group">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>পরবর্তী তারিখ</label>
                                        <input type="text" onchange="updateNoteWithData(this)" name="trialDate"
                                            id="trialDate" class="form-control form-control-sm "
                                            placeholder="দিন/মাস/তারিখ" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="trialTime" class="control-label">সময় </label>
                                    <input class="form-control  form-control-sm" type="time" name="trialTime"
                                        id="trialTime" value="13:45" id="example-time-input">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8" id="neworderPublish" style="display: none;">
                            <div class="row form-group">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="offenderGender" class="control-label"> সম্পূর্ণ আদেশ
                                            প্রকাশ</label>

                                        <div class="radio"><label>
                                                <input onchange="neworderPublishDate(this)" id="neworderPublishYse"
                                                    type="radio" name="orderPublishDecision" value="1"
                                                    class="orderPublishDecision" checked="checked">
                                                হ্যাঁ</label> <label class="ml-2"><input
                                                    onchange="neworderPublishDate(this)" id="neworderPublishNo"
                                                    type="radio" name="orderPublishDecision" value="0"
                                                    class="orderPublishDecision"> না</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" id="finalOrderPublishDate" style="display: block;">
                                    <div class="form-group">
                                        <label for="offenderGender" class="control-label"> সম্পূর্ণ আদেশ প্রকাশের
                                            তারিখ</label>

                                        <input type="text" name="finalOrderPublishDate"
                                            id="finalOrderPublishDateNow" class="form-control form-control-sm"
                                            placeholder="দিন/মাস/তারিখ" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</fieldset>
