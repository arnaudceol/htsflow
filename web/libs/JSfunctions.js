
function toggle(element){ // use this function to hide/see a specific DIV element
	document.getElementById(element).style.display = (document.getElementById(element).style.display == "none") ? "" : "none";
}

function toggleClass(class1, class2) {
	alert($(this).class);
	if ($(this).hasClass(class1)) {
		$(this).addClass(class2).removeClass(class1);
	} else {
		$(this).addClass(class1).removeClass(class2);
	}
	
}

function contains(a, obj) {
    for (var i = 0; i < a.length; i++) {
        if (a[i] === obj) {
            return true;
        }
    }
    return false;
}

function selectRow(row){
	var firstInput = row.getElementsByTagName('input')[0];
	firstInput.checked = !firstInput.checked;
}

function getElementsStartsWithId( id ) {
  var children = document.body.getElementsByTagName('*');
  var elements = [], child;
  for (var i = 0, length = children.length; i < length; i++) {
    child = children[i];
    if (child.id.substr(0, id.length) == id)
      elements.push(child.value);
  }
  return elements;
}

function controlPrimaryJob(buttonID) {
    var id_pre = document.getElementById('job').value;
    if (buttonID == 'redoPrimary') {
        var r = confirm( "Do you want to RESTART the primary analysis with ID: "+id_pre );
        if (r == true) {
            x = 1;
        } else {
            x = 0;
        }
        if ( x == 1 ) {
            document.getElementById("controlForm").submit();
        }
    } else if (buttonID == 'deletePrimary') {
        var r = confirm( "Do you want to DELETE the primary analysis with ID: "+id_pre );
        if (r == true) {
            x = 1;
        } else {
            x = 0;
        }
        if ( x == 1 ) {
            document.getElementById("controlForm").submit();
        }
    }
}

function controlSecondaryJob(buttonID) {
    var id_pre = document.getElementById('job').value;
    if (buttonID == 'redoSecondary') {
        var r = confirm( "Do you want to RESTART the secondary analysis with ID: "+id_pre );
        if (r == true) {
            x = 1;
        } else {
            x = 0;
        }
        if ( x == 1 ) {
            document.getElementById("controlForm").submit();
        }
    } else if (buttonID == 'deleteSecondary') {
        var r = confirm( "Do you want to DELETE the secondary analysis with ID: "+id_pre );
        if (r == true) {
            x = 1;
        } else {
            x = 0;
        }
        if ( x == 1 ) {
            document.getElementById("controlForm").submit();
        }
    }
}
