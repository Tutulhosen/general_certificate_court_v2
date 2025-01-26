@extends('layouts.default')

@section('styles')
    <style>
        .button {
            text-align: center;
            margin-top: 10px;
        }

        #startRecording_one {
            background-color: #fff;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #startRecording_one:hover {
            background-color: #f1f1f1;
        }


        img {
            filter: invert(50%);
        }

        small {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #555;
        }
    </style>
@endsection

@section('content')
    <!--begin::Row-->
    <div class="row">
        <div class="col-md-12">
            <!--begin::Card-->
            <div class="card card-custom gutter-b example example-compact">
                <div class="card-header">
                    <h3 class="card-title h2 font-weight-bolder">{{ $page_title }}</h3>
                    <div class="card-toolbar">
                        {{-- @include('appealTrial.inc._send_section') --}}
                    </div>
                </div>

                <!-- <div class="loadersmall"></div> -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (Session::has('withError'))
                    <div class="alert alert-danger text-center">
                        {{ Session::get('withError') }}
                    </div>
                @endif
                <!--begin::Form-->
                <form id="appealCase" action="" class="form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="appealId" value="{{ $appeal->id }}">
                    <input type="hidden" name="noteId" id="noteId" class="form-control" value="">

                    @if (count($nomineeCitizen) > 0)
                        <input type="hidden" name="is_nominee_attach" id="is_nominee_attach" class="form-control"
                            value="attached_required">
                    @else
                        <input type="hidden" name="is_nominee_attach" id="is_nominee_attach" class="form-control"
                            value="not_attached_not_required">
                    @endif

                    <!-- Added First 3 Short Order by default -->
                    @if ($appeal->appeal_status == 'SEND_TO_GCO')
                        <input value="1" type="hidden" name="defaulter_reg_notification">
                    @else
                        <input value="0" type="hidden" name="defaulter_reg_notification">
                    @endif

                    <div class="card-body">
                        <div class="row mb-8 ">
                            <div class="col-md-12">
                                <div class="example-preview">
                                    <ul class="nav nav-pills nav-fill">
                                        <li class="nav-item">
                                            <a class="nav-link px-0 active " id="regTab0" data-toggle="tab"
                                                href="#regTab_0">
                                                <span class="nav-icon">
                                                    <i class="flaticon2-chat-1"></i>
                                                </span>
                                                <span class="nav-text">মামলার তথ্য</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link px-0" id="regTab1" data-toggle="tab" href="#regTab_1">
                                                <span class="nav-icon">
                                                    <i class="flaticon2-chat-1"></i>
                                                </span>
                                                <span class="nav-text">আবেদনকারীর তথ্য</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link px-0" id="regTab2" data-toggle="tab" href="#regTab_2">
                                                <span class="nav-icon">
                                                    <i class="flaticon2-chat-1"></i>
                                                </span>
                                                <span class="nav-text">ঋণগ্রহীতার তথ্য</span>
                                            </a>
                                        </li>
                                        <!-- <li class="nav-item">
                                                                                                                                            <a class="nav-link px-0" id="regTab4" data-toggle="tab" href="#regTab_4">
                                                                                                                                                <span class="nav-icon">
                                                                                                                                                    <i class="flaticon2-chat-1"></i>
                                                                                                                                                </span>
                                                                                                                                                <span class="nav-text">আইনজীবীর তথ্য</span>
                                                                                                                                            </a>
                                                                                                                                        </li> -->
                                        @if (count($nomineeCitizen) > 0)
                                            <li class="nav-item">
                                                <a class="nav-link px-0 " id="regTab5" data-toggle="tab" href="#regTab_5">
                                                    <span class="nav-icon">
                                                        <i class="flaticon2-chat-1"></i>
                                                    </span>
                                                    <span class="nav-text">উত্তরাধিকারীর তথ্য</span>
                                                </a>
                                            </li>
                                        @endif

                                    </ul>
                                    <hr>
                                    <div class="tab-content mt-5" id="myTabContent4">

                                        @include('appealInitiate.inc._case_details')

                                        @include('appealInitiate.inc._applicant_info')
                                        @include('appealInitiate.inc._defaulter_info')
                                        @if (count($nomineeCitizen) > 0)
                                            @include('appealTrial.inc._nominee_info')
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                        <fieldset class="mb-8 p-7" style="background: none;" id="legalReportSection">
                            @include('appealTrial.inc._legalReportSection')
                        </fieldset>
                        @include('appealInitiate.inc._court_fee')
                        @include('appealInitiate.inc._previous_order_list')
                        @include('appealInitiate.inc._voice_to_text')
                        @include('appealTrial.inc._working_order_list')
                        <fieldset class=" mb-8">
                            <div class="rounded d-flex align-items-center justify-content-between flex-wrap px-5 py-0 mb-2">
                                <div class="d-flex align-items-center mr-2 py-2">
                                    <h3 class="mb-0 mr-8">সংযুক্তি</h3>
                                </div>
                            </div>
                            @forelse ($attachmentList as $key => $row)
                                <div class="form-group mb-2" id="deleteFile{{ $row->id }}">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <button class="btn bg-success-o-75"
                                                type="button">{{ en2bn(++$key) . ' - নম্বর :' }}</button>
                                        </div>
                                        {{-- <input readonly type="text" class="form-control" value="{{ asset($row->file_path . $row->file_name) }}" /> --}}
                                        <input readonly type="text" class="form-control"
                                            value="{{ $row->file_category ?? '' }}" />
                                        <div class="input-group-append">
                                            <a href="{{ asset($row->file_path . $row->file_name) }}" target="_blank"
                                                class="btn btn-sm btn-success font-size-h5 float-left">
                                                <i class="fa fas fa-file-pdf"></i>
                                                <b>দেখুন</b>
                                                {{-- <embed src="{{ asset('uploads/sf_report/'.$data[0]['case_register'][0]['sf_report']) }}" type="application/pdf" width="100%" height="600px" />  --}}
                                            </a>
                                            {{-- <a href="minarkhan.com" class="btn btn-success" type="button">দেখুন </a> --}}
                                        </div>
                                        {{-- <div class="input-group-append">
                                                <a href="javascript:void(0);" id="" onclick="deleteFile({{ $row->id }},{{ $id }} )" class="btn btn-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                    <b>মুছুন</b>
                                                </a>
                                            </div> --}}
                                    </div>
                                </div>
                            @empty
                                <div class="pt-5">
                                    <p class="text-center font-weight-normal font-size-lg">কোনো সংযুক্তি খুঁজে পাওয়া যায়নি
                                    </p>
                                </div>
                            @endforelse
                        </fieldset>
                        <fieldset class=" mb-8">
                            <div
                                class="rounded bg-success-o-100 d-flex align-items-center justify-content-between flex-wrap px-5 py-0">
                                <div class="d-flex align-items-center mr-2 py-2">
                                    <h3 class="mb-0 mr-8">সংযুক্তি (যদি থাকে)</h3>
                                </div>
                                <!--end::Info-->
                                <!--begin::Users-->
                                <div class="symbol-group symbol-hover py-2">
                                    <div class="symbol symbol-30 symbol-light-primary" data-toggle="tooltip"
                                        data-placement="top" title="" role="button"
                                        data-original-title="Add New File">
                                        <div id="addFileRow">
                                            <span class="symbol-label font-weight-bold bg-success">
                                                <i class="text-white fa flaticon2-plus font-size-sm"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Users-->
                            </div>
                            <div class="mt-3 px-5">
                                <table width="100%" class="border-0 px-5" id="fileDiv"
                                    style="border:1px solid #dcd8d8;">
                                    <tr></tr>
                                </table>
                                <input type="hidden" id="other_attachment_count" value="1">
                            </div>
                        </fieldset>
                        <div class="row buttonsDiv">
                            <div class="col-md-12 text-center">
                                <div class="form-group">
                                    <button id="orderPreviewBtn" type="button" class="btn btn-primary"
                                        data-toggle="modal" data-target="#exampleModal" disabled
                                        onclick="orderPreview()">
                                        প্রিভিউ ও সংরক্ষণ
                                    </button>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card-body-->
                </form>
            </div>
        </div>
        @include('appealTrial.inc.__modal')
        @include('appealTrial.inc.__orderPreview')
    </div>
@endsection

@section('styles')
@endsection

@section('scripts')
    @include('appealTrial.inc._script')
    <script>
        function validate(evt) {
            var theEvent = evt || window.event;
            // Handle paste
            if (theEvent.type === 'paste') {
                key = event.clipboardData.getData('text/plain');
            } else {
                // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]|\./;
            if (!regex.test(key)) {
                theEvent.returnValue = false;
                if (theEvent.preventDefault) theEvent.preventDefault();
            }
            // Get the value entered in amount_to_pay_as_remaining
            const remainingAmount = document.getElementById('amount_to_pay_as_remaining').value;
            if (remainingAmount.length > 2) {
                const costingAmount = parseInt(remainingAmount) * 2.5 / 100;
                document.getElementById('amount_to_pay_as_costing').value = costingAmount;

            } else {
                document.getElementById('amount_to_pay_as_costing').value = '';
            }
            const interestRate = document.getElementById('interestRate');

            if (interestRate.value) {
                const interestRateAmount = parseInt(remainingAmount) + (parseInt(remainingAmount) * parseFloat(interestRate
                    .value) / 100);
                const newTotalJari = interestRateAmount + parseFloat(document.getElementById('amount_to_pay_as_costing')
                    .value);

                document.getElementById('total_jari').value = newTotalJari;
            } else {
                const total_jari = parseFloat(document.getElementById(
                    'amount_to_pay_as_costing').value) + parseFloat(remainingAmount);
                document.getElementById('total_jari').value = String(total_jari)
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const voiceToTextField = document.getElementById('output');
            console.log('voiceToTextField', voiceToTextField)
            document.getElementById('voice_to_text').onclick = function() {
                voiceToTextField.style.display = 'block';
            };
            document.getElementById('voice_to_text_cross').onclick = function() {
                voiceToTextField.style.display = 'none';
            };
        });
    </script>
    <script>
        $('#startRecording_one').click(function() {
            const recognition = new webkitSpeechRecognition();
            recognition.lang = 'en-GB'
            recognition.onresult = (event) => {
                const voiceText = event.results[0][0].transcript;
                // // diagnostic.textContent = `Result received: ${color}`;
                // const voiceToTextField = document.getElementById('output_text_area');
                // voiceToTextField.value += (color + ' ');
                $.ajax({
                    url: `https://cors-anywhere.herokuapp.com/https://api.mymemory.translated.net/set?seg=${encodeURIComponent('Hello World!')}&tra=${encodeURIComponent('Bengali!')}&langpair=en|bn`,
                    method: 'GET',
                    success: function(response) {
                        console.log('Response:', response);
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });


            };
            recognition.start()
        })
    </script>
    {{-- <script src="https://cdn.socket.io/4.0.0/socket.io.min.js"></script>
    <script>
        $(function() {
            let ip_address = "https://voice.bangla.gov.bd";
            let port = "9394";
            let socket = io(ip_address + ':' + port, {
                transports: ['websocket'],
                upgrade: false
            });

            let uniqueWords = new Set(); // To store unique words
            let responseCount = 0; // Counter for responses received
            let sendAudioInterval; // Variable for the interval to send audio
            let currentIndex = 0; // Index for audio chunks
            let audioChunks = []; // Array to store audio chunks
            let recording = false; // State of recording
            let mediaRecorder; // MediaRecorder instance

            socket.on('connect', function() {
                console.log("Connected to socket");

                $('#startRecording_one').click(function() {
                    $('#status').text('Starting recording...');
                    $('#start_recording_div').css('display', 'none');
                    $('#stop_recording_div').css('display', 'block');

                    if (recording) {
                        console.log("Recording is already in progress.");
                        return; // Prevent starting again if already recording
                    }

                    navigator.mediaDevices.getUserMedia({
                            audio: true
                        })
                        .then(stream => {
                            mediaRecorder = new MediaRecorder(stream);
                            audioChunks = []; // Reset audio chunks for new recording

                            mediaRecorder.ondataavailable = function(event) {
                                audioChunks.push(event.data);
                            };

                            mediaRecorder.onstop = () => {
                                const rawBlob = new Blob(audioChunks, {
                                    type: 'audio/webm'
                                });
                                audioChunks = [];

                                convertToPCM(rawBlob).then((pcmData) => {
                                    const wavHeader = createWavHeader(pcmData
                                        .byteLength);
                                    const wavBlob = new Blob([wavHeader, pcmData], {
                                        type: 'audio/wav'
                                    });

                                    const reader = new FileReader();
                                    reader.readAsDataURL(wavBlob);
                                    reader.onloadend = () => {
                                        const base64String = reader.result
                                            .split(',')[1];
                                        let message = {
                                            index: currentIndex,
                                            audio: base64String,
                                            endOfStream: false // Set to false initially
                                        };

                                        // Send the audio message to the server
                                        socket.emit('audio_transmit', message,
                                            function(response) {
                                                console.log(
                                                    'Server response:',
                                                    response);
                                            });

                                        currentIndex++;
                                    };
                                });
                            };

                            mediaRecorder.start();
                            recording = true;

                            // Set up an interval to send audio data every 500 milliseconds
                            sendAudioInterval = setInterval(() => {
                                if (recording) {
                                    mediaRecorder
                                        .stop(); // Stop to capture the latest audio
                                    mediaRecorder
                                        .start(); // Start again for the next interval
                                }
                            }, 500);

                            $('#stopRecording').on('click', function() {
                                $('#start_recording_div').css('display', 'block');
                                $('#stop_recording_div').css('display', 'none');

                                clearInterval(sendAudioInterval);
                                if (recording) {
                                    mediaRecorder.stop();
                                    recording = false;
                                    $('#status').text(
                                        'Recording stopped.'
                                    ); // Update status when stopping

                                    // Send final message with endOfStream set to true
                                    let finalMessage = {
                                        index: currentIndex,
                                        audio: '', // No need to send audio again
                                        endOfStream: true // Indicate the end of stream
                                    };

                                    socket.emit('audio_transmit', finalMessage,
                                        function(response) {
                                            console.log('Final server response:',
                                                response);
                                        });
                                }
                            });

                        })
                        .catch(error => {
                            console.error('Error accessing microphone:', error);
                            $('#status').text('Error accessing microphone.');
                        });
                });
            });

            socket.on('result', (data) => {
                console.log(data); // Log the received data for debugging
                responseCount++; // Increment response count

                if (data.chunk === 'small_chunk') {
                    handleSmallChunk(data);
                } else if (data.chunk === 'large_chunk') {
                    handleLargeChunk(data);
                }

                // If three responses have been received, process the output
                if (responseCount === 3) {
                    console.log('come here responseCount', Array.from(uniqueWords).join(' '))
                    updateOutput(Array.from(uniqueWords).join(' ')); // Show unique words
                    uniqueWords.clear(); // Clear unique words for the next batch
                    responseCount = 0; // Reset response count after processing
                    console.log("Three responses received and processed.");
                }
            });

            function handleSmallChunk(data) {
                // Process small chunk data
                data.output.predicted_words.forEach(wordInfo => {
                    if (wordInfo.word && !uniqueWords.has(wordInfo.word)) {
                        uniqueWords.add(wordInfo.word); // Add unique word to set
                    }
                });
            }

            function handleLargeChunk(data) {
                // Process large chunk data
                data.output.predicted_words.forEach(wordInfo => {
                    if (wordInfo.word && !uniqueWords.has(wordInfo.word)) {
                        uniqueWords.add(wordInfo.word); // Add unique word to set
                    }
                });
            }

            function updateOutput(text) {
                // Update the output textarea with the unique text
                const voiceToTextField = document.getElementById('output_text_area');
                voiceToTextField.value += (text + ' ');
            }

            function createWavHeader(dataSize, sampleRate = 44100, numChannels = 1, bitsPerSample = 16) {
                const header = new ArrayBuffer(44);
                const view = new DataView(header);

                view.setUint32(0, 0x52494646, false); // "RIFF"
                view.setUint32(4, 36 + dataSize, true); // RIFF chunk size
                view.setUint32(8, 0x57415645, false); // "WAVE"

                view.setUint32(12, 0x666d7420, false); // "fmt " sub-chunk
                view.setUint32(16, 16, true); // Subchunk1Size (16 for PCM)
                view.setUint16(20, 1, true); // Audio format (PCM = 1)
                view.setUint16(22, numChannels, true); // Number of channels
                view.setUint32(24, sampleRate, true); // Sample rate
                view.setUint32(28, sampleRate * numChannels * (bitsPerSample / 8), true); // Byte rate
                view.setUint16(32, numChannels * (bitsPerSample / 8), true); // Block align
                view.setUint16(34, bitsPerSample, true); // Bits per sample

                view.setUint32(36, 0x64617461, false); // "data" sub-chunk
                view.setUint32(40, dataSize, true); // Data size

                return header;
            }

            function convertToPCM(blob) {
                return new Promise((resolve) => {
                    const audioContext = new(window.AudioContext || window.webkitAudioContext)();
                    const reader = new FileReader();

                    reader.onloadend = () => {
                        audioContext.decodeAudioData(reader.result, (buffer) => {
                            const channelData = buffer.getChannelData(0); // Assuming mono
                            const pcmArray = new Int16Array(channelData.length);

                            for (let i = 0; i < channelData.length; i++) {
                                pcmArray[i] = Math.min(1, channelData[i]) * 0x7FFF;
                            }

                            resolve(pcmArray.buffer);
                        });
                    };

                    reader.readAsArrayBuffer(blob);
                });
            }

            socket.on('connect_error', function(error) {
                console.error("Connection error:", error);
                $('#status').text('Socket connection error.');
            });
        });
    </script> --}}
@endsection
