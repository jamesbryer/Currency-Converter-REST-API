var url;

//function to populate the dropdown with live currencies using data from response.xml file
function populateDropdown() {
  //remove all existing options - only needed for refreshes after submit button pressed
  var select = document.getElementById("dropdown");
  while (select.options.length > 0) {
    select.remove(0);
  }

  var xhttp = new XMLHttpRequest();

  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      var xmlDoc = this.responseXML;
      var records = xmlDoc.getElementsByTagName("currency");
      //loop through each currency
      for (var i = 0; i < records.length; i++) {
        //if currency is live get data from it and add an option tag to dropdown
        if (records[i].getAttribute("live") == 1) {
          var specificChild =
            records[i].getElementsByTagName("code")[0].childNodes[0].nodeValue;
          var dropdown = document.getElementById("dropdown");
          var option = document.createElement("option");
          option.text = specificChild;
          option.value = specificChild;
          dropdown.add(option);
        }
      }
    }
  };
  xhttp.open("GET", "../response.xml", true);
  //prevents caching so dropdown populates with correct data on reload
  xhttp.setRequestHeader("Cache-Control", "no-cache, no-store, max-age=0");
  xhttp.send();
  //log to the console to show that the function has been called
  console.log("populateDropdown() called");
}

//function to send request to URL set in parameter
function loadDoc(url) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("xml_text").innerHTML = this.responseText;
      console.log(this.responseText);
    }
  };
  xhttp.open("GET", url, true);
  xhttp.send();
}

//send request to URL set by html form in index.php
function sendRequest(code, action) {
  url = "index.php?cur=" + code + "&" + "action=" + action;
  loadDoc(url);
}

//function to retrieve the selected value from the radio button selected
function getSelectedRadio() {
  var selectedValue;
  var radioButtons = document.getElementsByName("radio");
  for (var i = 0; i < radioButtons.length; i++) {
    if (radioButtons[i].checked) {
      selectedValue = radioButtons[i].value;
      break;
    }
  }
  console.log(selectedValue);
  return selectedValue;
}

//function to retrieve the selected value from the dropdown - pretty simple stuff really!
function getSelectedCur() {
  var selectedCur = document.getElementById("dropdown").value;
  console.log(selectedCur);
  return selectedCur;
}
