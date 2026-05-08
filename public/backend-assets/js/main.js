

অ্যাপ্লিকেশনে লগইন করে একটু ঘাটাঘাটি করুন কোন কিছু না বুঝলে ফোন করতে পারেন কোন সমস্যা নেই নিচে আপনার আইডি পাসওয়ার্ড দেয়া হলো যা দিয়ে লগইন করবেন। 

URL : https://paanbilash.com

USERNAME : forhad727072@gmail.com

PASSWORD : xxxxxxxx

পাসওয়ার্ড দেয়া হয়েছে আপনি আপনার পাসওয়ার্ডটি পরিবর্তন করে নিন। 



Create a dropdown where the date will be displayed

<select name="cmbOrderDate" class="form-control border-warning" required>
	<option value="">Select Order Date</option>
	@foreach($qOrderDate as $sOrderDate)
		@if(in_array(date('Y-m-d', strtotime($sOrderDate)), [date('Y-m-d'), date('Y-m-d', strtotime('+1 days'))])
		<option value="{{ date('Y-m-d', strtotime($sOrderDate)) }}">{{ date('d-m-Y', strtotime($sOrderDate)) }}</option>
		@endif
	@endforeach
</select>
        
Shows the date for the next seven days including today's date But no date later than today will show and not show friday date



0=Initiate,1=In process,2=Delivery in progress,3=Has been delivered,4=Cancel,5=Return
