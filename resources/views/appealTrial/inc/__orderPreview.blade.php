<!-- Button trigger modal -->


<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">আদেশের প্রিভিউ</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      @if ($user_court_info->role_id == 7 || $user_court_info->role_id == 10)
        <div class="modal-body">
          <div class="col-md-12" > test order</div>
        </div>
      @else
        <div class="modal-body">
          <div class="col-md-12" id="orderContaint"></div>
        </div>
      @endif
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">বন্ধ করুন </button>
        <button type="button" class="btn btn-primary" onclick="myFunction()"> আদেশ সংরক্ষণ করুন</button>
      </div>
    </div>
  </div>
</div>