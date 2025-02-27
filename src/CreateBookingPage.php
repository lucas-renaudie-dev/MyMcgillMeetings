<?php
// Written by Jeffrey Gan

// This file is intended to be loaded by index.php, thus it only contains the body and doesn't have a header
// i.e. index.php should have the line: include(CreateBookingPage.php)

include("delete-links/delete-create-link");
include("CreateBookingCss.txt");
?>

<div class="main-content" id="create-booking-body">
<h1> Create a new booking </h1>

    <form action="CreateBooking.php" method="post" id="form" name="form">
    <div class="left-side">
        <h2> Date and Time </h2>
        <div id="type-radio">
        <label for="event-type">Booking type:</label> <br>
        <input type="radio" name="event-type" value="SINGLE" checked="checked" onclick="onRadioClick('SINGLE')"> Single Day
        <input type="radio" name="event-type" value="WEEKLY" onclick="onRadioClick('WEEKLY')"> Weekly
        <input type="radio" name="event-type" value="MONTHLY" onclick="onRadioClick('MONTHLY')"> Monthly
        </div>

        <div id="date-time-inputs">    
        </div>
        
    </div>

    <div id="title-desc-form" class="right-side">
        <h2> Details </h2>
        <label for="title"> Title: </label> <br>
        <input type="text" name="title" class="title-field" minlength="1" maxlength="80" required> <br> <br>
        <label for="desc"> Description: </label> <br>
        <textarea type="text" name="desc" class="desc-field" minlength="0" maxlength="1000" placeholder="(optional)"></textarea> <br> <br>

        <input type="submit" value="Create new booking">
    </div>
    </form>
</div>

<script id="script-dt-inputs">
    let rowId = 0;
	let eventType = "";

	const singleDateTime = 
		'Start Time \
		<input type="time"  name="start-times[]" value="8:00" required> \
		Number of meeting(s) \
		<input type="number" name="num-meetings[]" min="1" max="1440" placeholder="Enter a number" required> \
		Duration of Meetings (min) \
		<input type="number" name="len-meetings[]" min="1" max="999999" placeholder="Enter a number" required>';

	const weeklyDateTimeRowContent = 
		'Every <select name="day-of-week[]" required> \
			<option value="Monday"> Monday </option> \
			<option value="Tuesday"> Tuesday </option> \
			<option value="Wednesday"> Wednesday </option> \
			<option value="Thursday"> Thursday </option> \
			<option value="Friday"> Friday </option> \
			<option value="Saturday"> Saturday </option> \
			<option value="Sunday"> Sunday </option> \
		</select> <br>' +
		singleDateTime;

	const monthSelectDayOfWeek =
		'<p> <select name="num-dow[]" class="month-dow-order"> \
			<option value="first"> 1st </option> \
			<option value="second"> 2nd </option> \
			<option value="third"> 3rd </option> \
			<option value="fourth"> 4th </option> \
		</select>  \
		<select name="day-of-week[]" class="month-dow"> \
			<option value="Monday"> Monday </option> \
			<option value="Tuesday"> Tuesday </option> \
			<option value="Wednesday"> Wednesday </option> \
			<option value="Thursday"> Thursday </option> \
			<option value="Friday"> Friday </option> \
			<option value="Saturday"> Saturday </option> \
			<option value="Sunday"> Sunday </option> \
		</select>  \
		of every month </p>';

	const monthSelectDayOfMonth =
		'<p> \
			On the \
			<input type="number" name="num-of-month[]" min="1" max="31" required style="width: 50px;"> \
			th day of every month <br> \
			<i style="font-size: 12px;"> (The date will be the last day of the month if it has fewer days than the input.) </i>\
		</p>';

	// Format today's date as YYYY-MM-DD
	const today = new Date();
	const today_str = today.getFullYear() + "-" + (today.getMonth()+1).toString().padStart(2, '0') + "-" + today.getDate().toString().padStart(2, '0');

	const startDateInput = '<input type="date" id="start-date" name="start-date" min="' + today_str + '" required>';
	const endDateInput = '<input type="date" id="end-date" name="end-date" min="' + today_str + '" required>';

	// validate function has to be loaded AFTER or else it can't find the dynamic document elements
	const validationFunction = 
		'function validateDates (){ \
			let eventType = document.querySelector("input[name=event-type]:checked").value; \
			if (eventType !== "SINGLE") { \
				let form = document.forms["form"]; \
				let start_date = new Date(form["start-date"].value); \
				let end_date = new Date(form["end-date"].value); \
				let err_elmt = document.getElementById("errormsg-date"); \
				if (start_date.valueOf() > end_date.valueOf()) { \
					err_elmt.style.display="block"; \
					err_elmt.innerText="End date cannot be before start date."; \
					return false; \
				} \
				err_elmt.style.display="none"; err_elmt.innerText="";\n \
				return true;\n \
			} \
			return true; \
		}';


	let first = true;

	onRadioClick(document.querySelector('input[name=event-type]:checked').value);

	function onRadioClick(newEventType) {
		if (newEventType == eventType) {
			return;
		}
		eventType = newEventType;
		rowId = 0;
		document.getElementById("date-time-inputs").innerHTML = "";
		
		if (eventType == 'SINGLE'){
			showSingleDTInput();
		} else if (eventType == 'WEEKLY') {
			showWeeklyDTInput();
		} else if (eventType == 'MONTHLY') {
			showMonthlyDTInput();
		} else {
			document.getElementById("date-time-inputs").innerHTML = "Error: unknown event type";
		}

		/*
		Every time the type is changed, the validation script has to be reloaded
		or else it cannot get some elements that were added dynamically
		*/
		if (!first) {
			let old_script = document.getElementById("script-validate");
			old_script.remove();
		} else {
			first = false;
		}

		let validate_script = document.createElement("script");
		validate_script.id = "script-validate";
		validate_script.innerHTML = validationFunction;
		document.getElementById("create-booking-body").appendChild(validate_script);
		document.getElementById("form").setAttribute("onsubmit", "return validateDates()");
	}

	function getMonthSelectorValue(selectObject, id) {
		var val = selectObject.value;
		if (val === "DOW") {
			document.getElementById("dtmonth-" + id).innerHTML = monthSelectDayOfWeek;
		} else if (val === "DOM") {
			document.getElementById("dtmonth-"+ id).innerHTML = monthSelectDayOfMonth;
		} else {
			console.log("ERROR");
		}
	}

	// Add a new Weekly row of inputs
	function onAddWeeklyTimeRowBtnClick () {
		const rowInnerHTML = 
			'<button type="button" class="dt-button" onclick="onDeleteTimeRowBtnClick(' + rowId + ')"> - </button> ' + 
			weeklyDateTimeRowContent;

		const row = document.createElement('div');
		row.id = 'dtrow-' + rowId;
		row.classList.add("dt-row");
		row.innerHTML = rowInnerHTML;

		document.getElementById("dtrows").appendChild(row);
		rowId = rowId + 1;
	}

	// Add a new Monthly row of inputs
	function onAddMonthlyTimeRowBtnClick () {
		const rowInnerHTML = 
			'<button type="button" class="dt-button" onclick="onDeleteTimeRowBtnClick(' + rowId + ')"> - </button> \
			<select class="month-option" name="month-option[]" onchange="getMonthSelectorValue(this,' + rowId + ')"> \
				<option value="DOW"> Day of the week </option> \
				<option value="DOM"> Day of the month</option> \
			</select> \
			<div id="dtmonth-' + rowId + '">' + 
			monthSelectDayOfWeek +
			'</div>' +
			singleDateTime;

		const row = document.createElement('div');
		row.id = 'dtrow-' + rowId;
		row.innerHTML = rowInnerHTML;

		document.getElementById("dtrows").appendChild(row);
		row.classList.add("dt-row");
		rowId = rowId + 1;
	}

	// Delete the row that contained the button that caused the event
	function onDeleteTimeRowBtnClick(id) {
		const row = document.getElementById("dtrow-" + id);
		row.remove();
	}

	//
	// Replace the inputs with the selected type
	//
	function showSingleDTInput() {
		const w = 
			'<div class="single-dt dt-input"> \
				<p class="dates-input"> On ' +
					startDateInput +
				'</p> \
				<p>' + 
					singleDateTime + 
				'</p> \
			</div>';
		document.getElementById("date-time-inputs").innerHTML = w;
	}

	function showWeeklyDTInput() {
		const w='<div class="weekly-dt dt-input">' + 
					'<p id="errormsg-date" style="display: none; color: red;"> </p>' +
					'<p class="dates-input"> Between' + startDateInput + ' and ' + endDateInput + ' </p>' +
					'<div id="dtrows">' + 
					'</div>' + 
					'<button type="button" class="dt-button" onclick="onAddWeeklyTimeRowBtnClick()"> + </button>' +
				'</div>';
		document.getElementById("date-time-inputs").innerHTML = w;
		onAddWeeklyTimeRowBtnClick();
	}

	function showMonthlyDTInput() {
		const w='<div class="monthly-dt dt-input"> \
					<p id="errormsg-date" style="display: none; color: red;"> </p> \
					<p class="dates-input"> Between' + startDateInput + ' and ' + endDateInput + ' </p> \
					<div id="dtrows"> \
					</div> \
					<button type="button" class="dt-button" onclick="onAddMonthlyTimeRowBtnClick()"> + </button> \
				</div>';
		document.getElementById("date-time-inputs").innerHTML = w;
		onAddMonthlyTimeRowBtnClick();
	}
</script>