<script>
    // ========== Form Submission ========= Start =========



    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    function isPhone(phone) {
        var regex = /(^(\+8801|8801|01|008801))[1|3-9]{1}(\d){8}$/;
        return regex.test(phone);
    }

    function myFunction() {
        
        let permission = true;
        if ($('#nomineeNid_1').val() != "") {
            $('.validation').each(function() {

                if ($(this).val() == '') {
                    $(this).addClass('waring-border-field');
                    $(this).next('.required_message').removeClass('hide');
                    $(this).next('.required_message').addClass('show warning-message-alert');
                    permission = false;
                } else {
                    $(this).removeClass('waring-border-field');
                    $(this).next('.required_message').addClass('hide');
                    $(this).next('.required_message').removeClass('show warning-message-alert');
                }


                $(this).on('keyup', function() {
                    if ($(this).val() == '') {
                        $(this).addClass('waring-border-field');
                        $(this).next('.required_message').removeClass('hide');
                        $(this).next('.required_message').addClass(
                            'show warning-message-alert');
                        permission = false;
                    } else if ($(this).hasClass("email")) {
                        if (!isEmail($(this).val())) {
                            permission = false;
                            $(this).addClass('waring-border-field');
                            $(this).next('.required_message').removeClass('hide');
                            $(this).next('.required_message').addClass(
                                'show warning-message-alert');
                            $(this).next('.required_message').text(
                                'Invalid Email Address');
                        } else {
                            $(this).removeClass('waring-border-field');
                            $(this).next('.required_message').addClass('hide');
                            $(this).next('.required_message').removeClass(
                                'show warning-message-alert');

                        }
                    } else if ($(this).hasClass("phone")) {
                        if (!isPhone($(this).val())) {
                            permission = false;
                            $(this).addClass('waring-border-field');
                            $(this).next('.required_message').removeClass('hide');
                            $(this).next('.required_message').addClass(
                                'show warning-message-alert');
                            $(this).next('.required_message').text(
                                'Invalid phone number');
                        } else {
                            $(this).removeClass('waring-border-field');
                            $(this).next('.required_message').addClass('hide');
                            $(this).next('.required_message').removeClass(
                                'show warning-message-alert');

                        }
                    } else {
                        $(this).removeClass('waring-border-field');
                        $(this).next('.required_message').addClass('hide');
                        $(this).next('.required_message').removeClass(
                            'show warning-message-alert');
                    }
                })



            });
        }

        if (permission) {
            $('form#appealCase').submit();
        } else {
            Swal.fire('উত্তরাধিকারীর তথ্যগুলো সঠিকভাবে প্রদান করুন')
        }





        // var textX = $('#button_text_submit').val();
        // if (typeof textX !== 'undefined') {

        //     var button_text_submit = "আপনি কি " + textX + "  করতে চান?";
        // } else {
        //     var button_text_submit = "আপনি কি সংরক্ষণ  করতে চান?";
        // }

        // Swal.fire({
        //         title: button_text_submit,
        //         icon: "warning",
        //         showCancelButton: true,
        //         confirmButtonText: "হ্যাঁ",
        //         cancelButtonText: "না",
        //     })
        //     .then(function(result) {
        //         if (result.value) {




        //         }
        //     });
    }

    function formSubmit() {

        // $('#status').val('SEND_TO_GCO');
        myFunction();
    }
    // ========== Form Submission ========= End =========

    // ========== New Case or Old ========= Start =========
    $('input[type=radio][name=caseEntryType]').change(function() {
        if (this.value == 'NEW') {
            $("#prevCaseDiv").addClass("d-none");
        } else {
            $("#prevCaseDiv").removeClass("d-none");
        }
    });
    // ==============New Case or Old ========= end =========

    // ================Activities ========= Start =========
    function primaryNote() {
        var organaization = $('#applicantOrganization_1').val() ? $('#applicantOrganization_1').val() :
            "প্রতিষ্ঠানের নাম ";
        var case_date = $('#case_date').val() ? $('#case_date').val() : ' তারিখ ';
        case_date = NumToBangla.replaceNumbers(case_date);
        var defaulterName = $('#defaulterName_1').val() ? $('#defaulterName_1').val() : ' খাতকের নাম ';
        var totalLoanAmount = $('#totalLoanAmount').val() ? $('#totalLoanAmount').val() : '  টাকার পরিমাণ ';
        totalLoanAmount = NumToBangla.replaceNumbers(totalLoanAmount);
        var totalLoanAmountText = $('#totalLoanAmountText').val() ? $('#totalLoanAmountText').val() : '';
        var lawSection = $('#lawSection').val() ? $('#lawSection').val() : ' সরকারি পাওনা আদায় আইন, ১৯১৩ এর ৫ ধারা ';
        var GenActivitiesDefault = organaization + " হতে " + case_date + " তারিখে " + defaulterName + " এর নিকট হতে " +
            totalLoanAmount + " (" + totalLoanAmountText + ") টাকা আদায়ের জন্য " + lawSection +
            " মতে একটি অনুরোধপত্র পাওয়া গিয়েছে। \n\nদেখলাম। দাবী আদায়যোগ্য বিবেচিত হওয়ায় ১০ নং রেজিস্টার বহিতে লিপিবদ্ধ করে সার্টিফিকেট রিকুইজিশনে স্বাক্ষর করা হল। সার্টিফিকেট খাতকের প্রতি ১০(ক) ধারার নোটিশ জারি করা হোক। আগামি (০১ মাসের  মধ্যে) প্রসেস সার্ভারকে নোটিশ জারির এস আর দাখিল করার জন্য বলা হল।";
        // var activitiesDefault = "প্রতিষ্ঠানের নাম হতে তারিখে খাতকের নাম এর নিকট হতে টাকার পরিমাণ টাকা আদায়ের জন্য সরকারি পাওনা আদায় আইন, ১৯১৩ এর ৫ ধারা মতে একটি অনুরোধপত্র পাওয়া গিয়েছে। <br/> দেখলাম। দাবী আদায়যোগ্য বিবেচিত হওয়ায় ১০ নং রেজিস্টার বহিতে লিপিবদ্ধ করে সার্টিফিকেট রিকুইজিশনে স্বাক্ষর করা হল। সার্টিফিকেট খাতকের প্রতি ১০(ক) ধারার নোটিশ জারি করা হোক। আগামি (০১ মাসের  মধ্যে) প্রসেস সার্ভারকে নোটিশ জারির এস আর দাখিল করার জন্য বলা হল।";
        $('#note').val(GenActivitiesDefault);
    }
    $("#applicantOrganization_1").change(function() {
        primaryNote();
    });
    $("#case_date").change(function() {
        primaryNote();
    });
    $("#defaulterName_1").change(function() {
        primaryNote();
    });
    $("#totalLoanAmount").change(function() {
        setTimeout(function() {
            primaryNote();
        }, 2000);
    });
    $("#totalLoanAmountText").change(function() {
        primaryNote();
    });
    $("#lawSection").change(function() {
        primaryNote();
    });
    // ================= Activities ========= end =========

    // ============= Add Attachment Row ========= start =========
    $("#addFileRow").click(function(e) {
        addFileRowFunc();
    });
    //add row function
    /* function addFileRowFunc() {
         var count = parseInt($('#other_attachment_count').val());
         $('#other_attachment_count').val(count + 1);
         var items = '';
         items += '<tr>';
         items += '<td><input type="text" name="file_type[]" class="form-control form-control-sm" placeholder=""></td>';
         items += '<td><div class="custom-file"><input type="file" name="file_name[]" onChange="attachmentTitle(' +
             count + ')" class="custom-file-input" id="customFile' + count +
             '" /><label class="custom-file-label custom-input' + count + '" for="customFile' + count +
             '">ফাইল নির্বাচন করুন</label></div></td>';
         items += '<td width="40"><a href="javascript:void();" class="btn btn-sm btn-danger font-weight-bolder pr-2" onclick="removeBibadiRow(this)"> <i class="fas fa-minus-circle"></i></a></td>';
         items += '</tr>';
         $('#fileDiv tr:last').after(items);
     }*/
    function addFileRowFunc() {
        var count = parseInt($('#other_attachment_count').val());
        $('#other_attachment_count').val(count + 1);
        var items = '';
        items += '<tr>';
        items +=
            '<td><input type="hidden" name="is_payment_file[]" value="not_payment_file"><input type="text" name="file_type[]" class="form-control form-control-sm" placeholder="ফাইলের নাম দিন" id="file_name_important' +
            count + '" ></td>';
        items += '<td><div class="custom-file"><input type="file" name="file_name[]" onChange="attachmentTitle(' +
            count + ',this)" class="custom-file-input" id="customFile' + count +
            '" /><label class="custom-file-label custom-input' + count + '" for="customFile' + count +
            '">ফাইল নির্বাচন করুন </label></div></td>';
        items +=
            '<td width="40"><a href="javascript:void();" class="btn btn-sm btn-danger font-weight-bolder pr-2" onclick="removeBibadiRow(this)"> <i class="fas fa-minus-circle"></i></a></td>';
        items += '</tr>';
        $('#fileDiv tr:last').after(items);
    }
    //Attachment Title Change
    /* function attachmentTitle(id) {
         var value = $('#customFile' + id).val();
         $('.custom-input' + id).text(value);
     }*/
    function attachmentTitle(id, obj) {
        // var value = $('#customFile' + id).val();
        var value = $('#customFile' + id)[0].files[0];

        const fsize = $('#customFile' + id)[0].files[0].size;
        const file_size = Math.round((fsize / 1024));

        var file_extension = value['name'].split('.').pop().toLowerCase();

        if ($.inArray(file_extension, ['pdf', 'docx']) == -1) {
            Swal.fire(

                'ফাইল ফরম্যাট PDF,docx হতে হবে ',

            )
            $(obj).closest("tr").remove();
        }
        if (file_size > 2048) {
            Swal.fire(

                'ফাইল সাইজ অনেক বড় , ফাইল সাইজ ২ মেগাবাইটের কম হতে হবে',

            )
            $(obj).closest("tr").remove();
        }

        var custom_file_name = $('#file_name_important' + id).val();
        if (custom_file_name == "") {
            Swal.fire(

                'ফাইল এর প্রথমে যে নাম দেয়ার field আছে সেখানে ফাইল এর নাম দিন ',

            )
            $(obj).closest("tr").remove();
        }

        $('.custom-input' + id).text(value['name']);
    }
    //remove Attachment
    function removeBibadiRow(id) {
        $(id).closest("tr").remove();
    }
    // =============== Add Attachment Row ===================== end =========================

    // Number to Bangla Word ========================= start ====================
    $("#totalLoanAmount").change(function() {
        var amount = $("#totalLoanAmount").val();
        var enAmount = NumToBangla.replaceBn2EnNumbers(amount);
        var num = parseInt(enAmount);
        if (num.toString().length < 16) {
            $("#totalLoanAmountText").val(NumToBangla.convert(num));
        } else {
            toastr.error('টাকার পরিমাণ অনেক দীর্ঘ', "Error");
        }
    });
    $("#totalLoanAmount").keyup(function() {
        var amount = $("#totalLoanAmount").val();
        $("#totalLoanAmount").val(NumToBangla.replaceNumbers(amount));
    });
    // Number to Bangla Word ========================= end ====================

    // Multiple Nominee ================================= start ==============================
    //remove Nominee
    $("#RemoveNominee").on('click', function() {
        var elements = parseInt($('#NomineeCount').val());
        if (elements > 1) {

            var countNegative = parseInt($('#NomineeCount').val());
            Swal.fire({
                title: 'বাতিল করতে চান ?',
                icon: 'info',
                confirmButtonText: 'Log in'
            }).then((result) => {
                if (result['isConfirmed']) {
                    $('.cloneNomenee_' + elements).remove()
                }
            })


            //$('.cloneNomenee_' + countNegative).remove()
            $('#NomineeCount').val(--countNegative);
        } else {
            console.log('fasle');
            Swal.fire({
                position: "top-right",
                icon: "error",
                title: 'আবেদনকারীর তথ্য সর্বনিম্ম একটি থাকতে হবে',
                showConfirmButton: false,
                timer: 1500,
            });
        }

    });

    //add Nominee
    @include('appealInitiate._nominee_multiple');
    // Multiple Nominee ================================= End ==============================

   


    // common datepicker =============== start
    $('.common_datepicker').datepicker({
        format: "dd/mm/yyyy",
        todayHighlight: true,
        orientation: "bottom left"
    });
   

    function updateNote(a) {
        var order_id = $(a).val();
        if (order_id == 27 || order_id == 28 || order_id == 29 || order_id == 36) {
            $("#paymentcollection_form_details").show();
        } else {
            $("#paymentcollection_form_details").hide();

        }

        createPayementFileField(order_id);




        if (1 == $(a).is(":checked")) {
            var description = $(a).attr("desc");
            var className = $(a).attr("name");
            // var data = $("#note").val() + "<p class='"+className+"'>" + description +"</p>";
            var data = $("#note").val() + "\n" + description;
            $("#note").val(data)
            $("#orderPreviewBtn").attr('disabled', false);
        }

        if (order_id == 1) {
            makeDynamicrequisition();
        }

        makeOrderWithClean(order_id);
    }

    function makeOrderWithClean(order_id) {


        $("#note").val('');
        var data = $('#shortOrder_' + order_id).attr("desc")+"\n\n"+"সদয় পরবর্তী আদেশের জন্যে";;

        $("#note").val(data)
    }

    function makeDynamicrequisition() {
        $.ajax({
            url: '{{ route('appeal.make.autofill.ruisition') }}',
            method: 'post',
            data: {

                appeal_id: $("input[name='appealId']").val(),
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {

                if (response.success == 'success') {
                    $("#note").val('');
                    var data =
                        "অদ্য নথি পেশ করা হলো । " +
                        "\n\n" + response.message +
                        "\n\n"+"সদয় পরবর্তী আদেশের জন্যে";
                    $("#note").val(data)
                    recuision_global = data;
                }
            }
        });
    }


    $('#trialDate,#report_date,#finalOrderPublishDateNow').datepicker({
        startDate: new Date(),
        format: "dd/mm/yyyy",
        todayHighlight: true,
        orientation: "bottom left"
    });








    function addDatePicker(id) {
        $(".common_datepicker_1").datepicker({
            format: 'yyyy/mm/dd'

        });
    }

    function orderPreview() {
        // console.log($("#note").val());
        var description = $("#note").val();
        $("#orderContaint").html(description);
    }


   



    function nid_data_pull_warning_fun() {
        Swal.fire(
            '',
            'অনুগ্রহ পূর্বক সংশ্লিষ্ট ব্যাক্তির জাতীয় পরিচয়পত্র নম্বর এবং জন্ম তারিখ প্রদান করুন ( ফর্ম এর উপরের দিকে দেখুন )। জাতীয় পরিচয়পত্র থেকে পিতার নাম, মাতার নাম, লিঙ্গ, ঠিকানা পেয়ে যাবেন যা পরিবর্তনযোগ্য নয় ।',
            'question'


        )
    }


    // $('.show_hide_attendence_btn').on('click',function(){
    //     if($(this).is(':checked') == false)
    //     {
    //         $(this).hide();
    //     }else
    //     {
    //         $(this).show(); 
    //     }
    // })

    function AttPrintHideShow(type) {
        var att = $('input[name="citizenAttendance[attendance][' + type + ']"]:checked').val();

        if (att == 'ABSENT') {
            $("#attVic_" + type).addClass("d-none");
        } else {
            $("#attVic_" + type).removeClass("d-none");
        }
        // console.log(att);
    }


    function english2bangla(input) {
        var finalEnlishToBanglaNumber = {
            '0': '০',
            '1': '১',
            '2': '২',
            '3': '৩',
            '4': '৪',
            '5': '৫',
            '6': '৬',
            '7': '৭',
            '8': '৮',
            '9': '৯'
        };

        String.prototype.getDigitBanglaFromEnglish = function() {
            var retStr = this;
            for (var x in finalEnlishToBanglaNumber) {
                retStr = retStr.replace(new RegExp(x, 'g'), finalEnlishToBanglaNumber[x]);
            }
            return retStr;
        };


        return input.getDigitBanglaFromEnglish();
    }

    $(document).on('keyup', '#court_fee_amount', function(e) {
        $(this).val(english2bangla($(this).val()));
    })
    $(document).on('keyup', '#court_fee_amount', function(e) {
        var field_value = $(this).val();
        let csrf = '{{ csrf_token() }}';
        $.ajax({
            url: '{{ route('number.field.check') }}',
            method: 'post',
            data: JSON.stringify({
                court_fee_amount: field_value,
                _token: csrf
            }),
            cache: false,
            contentType: 'application/json',
            processData: false,
            dataType: 'json',
            success: function(res) {

                if (!res.is_numeric) {
                    alert('শুধুমাত্র সংখ্যা দিন');
                    $('#court_fee_amount').val('');
                }
            }
        });

    })

    $(document).on('keyup', '#TodayPaymentPaymentCollection', function(e) {
        $(this).val(english2bangla($(this).val()));
    })
    $(document).on('keyup', '#TodayPaymentPaymentCollection', function(e) {
        var field_value = $(this).val();
        var TotalRemainingPaymentPaymentCollection = $('#TotalRemainingPaymentPaymentCollectionBckp').val();
        let csrf = '{{ csrf_token() }}';
        $.ajax({
            url: '{{ route('number.field.check.remainig.taka') }}',
            method: 'post',
            data: JSON.stringify({
                TodayPaymentPaymentCollection: field_value,
                totalDemandPaymentCollection: $('#totalDemandPaymentCollection').val(),
                collectSoFarPaymentCollection: $('#collectSoFarPaymentCollection').val(),
                _token: csrf
            }),
            cache: false,
            contentType: 'application/json',
            processData: false,
            dataType: 'json',
            success: function(res) {

                if (!res.is_numeric) {
                    alert('শুধুমাত্র সংখ্যা দিন');
                    $('#TodayPaymentPaymentCollection').val('');
                    $('#TotalRemainingPaymentPaymentCollection').val(
                        TotalRemainingPaymentPaymentCollection);
                    var data = "অদ্য নথি পেশ করা হলো । "
                    $("#note").val(data)
                }
                if (res.is_overflow) {
                    alert('আপনি অতিরিক্ত টাকা দিয়েছন');
                    $('#TodayPaymentPaymentCollection').val('');
                    $('#TotalRemainingPaymentPaymentCollection').val(
                        TotalRemainingPaymentPaymentCollection);
                    var data = "অদ্য নথি পেশ করা হলো । "
                    $("#note").val(data)
                }
                if (!res.is_overflow && res.is_numeric) {
                    $('#TotalRemainingPaymentPaymentCollection').val(res
                        .TotalRemainingPaymentPaymentCollection);
                    if ($('.shortOrderCheckBox:checked').val() == 27) {
                        var data =
                            "অদ্য নথি পেশ করা হলো । " + $('#shortOrder_27').attr('desc') +
                            "\n\n" + res.message;
                        $("#note").val(data)
                    }

                }
            }
        });

    })

    function ReportFormSubmit(formId) {
        console.log('check');
        $("#" + formId + " #submit").addClass('spinner spinner-dark spinner-left disabled');
        var form = $("#" + formId);

        var formData = new FormData(form[0]);
        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = ((evt.loaded / evt.total) * 100);
                        $(".progress-bar").width(percentComplete + '%');
                        $(".progress-bar").html(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            type: 'POST',
            url: "{{ route('appeal.trial.report_add') }}",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSubmit: function() {
                $(".progress-bar").width('0%');
                $("#uploadStatus").html('Uploading FILE');
            },
            success: (data) => {
                // form[0].reset();
                if (data.error == "error") {

                    toastr.error(data.error_details);
                    $("#" + formId + " #submit").removeClass(
                        'spinner spinner-white spinner-right disabled');
                } else {

                    toastr.success(data.success, "Success");

                    // console.log(data.html);
                    // $('.ajax').remove();
                    $('#legalReportSection').empty();
                    $('#legalReportSection').append(data.data)
                    // $('#hearing_add_button_close').click()
                    $("#" + formId + " #submit").removeClass(
                        'spinner spinner-white spinner-right disabled');
                    $('.modal').modal('hide');
                    form[0].reset();
                }



            },
            error: function(data) {

                $("#" + formId + " #submit").removeClass('spinner spinner-white spinner-right disabled');

            }
        });
    }

    $("form#appealCase").submit(function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        Swal.showLoading()
        $.ajax({
            url: '{{ route('appeal.store') }}',
            method: 'post',
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                console.log(response, 'from sotre')
                if (response.status == 'error') {
                    $('#exampleModal').modal('hide');
                    Swal.fire(response.message);
                    toastr.error(response.error);
                    Swal.fire({
                        text: response.message,

                    });
                    //scrollCreate(response.div_id);

                } else {

                    $('#exampleModal').modal('hide');
                    Swal.close();
                    toastr.success(response.success);
                    window.location.replace('/dashboard');
                }
            }
        });
    });

    $('#is_attendence_required').on('change', function() {
        if ($(this).val() == "attendence_required") {
            $('#is_attendence_required_div').show()
        } else {
            $('#is_attendence_required_div').hide()
        }
    });

    function createPayementFileField(order_id) {
        $('.default_file_add').remove();

        if (order_id == 27 || order_id == 36) {

            var count = 0;
            $('#other_attachment_count').val(count + 1);
            var items = '';
            items += '<tr class="default_file_add">';
            items +=
                '<td><input type="hidden" name="is_payment_file[]" value="payment_file"><input type="text" name="file_type[]" class="form-control form-control-sm payment_file" value="টাকা আদায়ের রশিদ"  id="file_name_important' +
                count + '" ></td>';
            items += '<td><div class="custom-file"><input type="file" name="file_name[]" onChange="attachmentTitle(' +
                count + ',this)" class="custom-file-input" id="customFile' + count +
                '" /><label class="custom-file-label custom-input' + count + '" for="customFile' + count +
                '">ফাইল নির্বাচন করুন </label></div></td>';
            items += '</tr>';
            $('#fileDiv tr:last').after(items);

        } else if (order_id == 28 || order_id == 29) {
            var valueFieldarray = [{
                    name_of_slip: 'টাকা আদায়ের রশিদ',
                    is_payment_file: 'payment_file',
                    classNameSelector: 'payment_file'
                },
                {
                    name_of_slip: 'নিষ্পত্তি আবেদন',
                    is_payment_file: 'not_payment_file',
                    classNameSelector: 'close_request'
                }
            ];
            var count = 0;
            $.each(valueFieldarray, function(index, val) {
                count = index + 1;
                var items = '';
                items += '<tr class="default_file_add">';
                items +=
                    '<td><input type="hidden" name="is_payment_file[]" value="' + val.is_payment_file +
                    '"><input type="text" name="file_type[]" class="form-control form-control-sm "' + val
                    .classNameSelector + '" value="' + val.name_of_slip + '"  id="file_name_important' +
                    count + '" ></td>';
                items +=
                    '<td><div class="custom-file"><input type="file" name="file_name[]" onChange="attachmentTitle(' +
                    count + ',this)" class="custom-file-input" id="customFile' + count +
                    '" /><label class="custom-file-label custom-input' + count + '" for="customFile' + count +
                    '">ফাইল নির্বাচন করুন </label></div></td>';
                items += '</tr>';
                $('#fileDiv tr:last').after(items);
            });
            $('#other_attachment_count').val(count + 1);
        } else if (order_id == 33) {
            var count = 0;
            $('#other_attachment_count').val(count + 1);
            var items = '';
            items += '<tr class="default_file_add">';
            items +=
                '<td><input type="hidden" name="is_payment_file[]" value="not_payment_file"><input type="text" name="file_type[]" class="form-control form-control-sm close_request" value="খাতকের দাবী অস্বীকার স্ক্যান কপি"  id="file_name_important' +
                count + '" ></td>';
            items += '<td><div class="custom-file"><input type="file" name="file_name[]" onChange="attachmentTitle(' +
                count + ',this)" class="custom-file-input" id="customFile' + count +
                '" /><label class="custom-file-label custom-input' + count + '" for="customFile' + count +
                '">ফাইল নির্বাচন করুন </label></div></td>';
            items += '</tr>';
            $('#fileDiv tr:last').after(items);
        }
    }


    $('#search_short_order_important').on('keyup', function() {
        $.each($(".case_short_decision_data"), function(key, value) {
            if ($(this).data('string').indexOf($('#search_short_order_important').val()) >= 0) {
                $('.radio_id_' + $(this).data('row_id_index')).show();
            } else {
                $('.radio_id_' + $(this).data('row_id_index')).hide();
            }

        });
    })
</script>
