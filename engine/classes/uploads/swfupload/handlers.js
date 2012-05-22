var totaluploadedfiles = 0;

function fileQueued(a) {
  try {
    var d = new FileProgress(a, this.customSettings.progressTarget);
    d.setStatus("\u0412 \u043e\u0447\u0435\u0440\u0435\u0434\u0438 ...");
    d.toggleCancel(true, this)
  }catch(b) {
    this.debug(b)
  }
}
function fileQueueError(a, d, b) {
  try {
    if(d === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
      alert("\u0412\u044b \u0432\u044b\u0431\u0440\u0430\u043b\u0438 \u0441\u043b\u0438\u0448\u043a\u043e\u043c \u043c\u043d\u043e\u0433\u043e \u0444\u0430\u0439\u043b\u043e\u0432.\n" + (b === 0 ? "\u0412\u044b \u043f\u0440\u0435\u0432\u044b\u0441\u0438\u043b\u0438 \u043b\u0438\u043c\u0438\u0442." : "\u0412\u044b \u043c\u043e\u0436\u0435\u0442\u0435 \u0432\u044b\u0431\u0440\u0430\u0442\u044c " + (b > 1 ? "\u043d\u0435 \u0431\u043e\u043b\u0435\u0435 " + b + " \u0444\u0430\u0439\u043b\u043e\u0432." : 
      "\u043e\u0434\u0438\u043d \u0444\u0430\u0439\u043b.")))
    }else {
      var c = new FileProgress(a, this.customSettings.progressTarget);
      c.setError();
      c.toggleCancel(false);
      switch(d) {
        case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
          c.setStatus("\u0424\u0430\u0439\u043b \u0441\u043b\u0438\u0448\u043a\u043e\u043c \u0431\u043e\u043b\u044c\u0448\u043e\u0439.");
          this.debug("Error Code: File too big, File name: " + a.name + ", File size: " + a.size + ", Message: " + b);
          break;
        case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
          c.setStatus("\u041d\u0435\u0432\u043e\u0437\u043c\u043e\u0436\u043d\u043e \u0437\u0430\u0433\u0440\u0443\u0437\u0438\u0442\u044c \u0444\u0430\u0439\u043b \u043d\u0443\u043b\u0435\u0432\u043e\u0433\u043e \u0440\u0430\u0437\u043c\u0435\u0440\u0430.");
          this.debug("Error Code: Zero byte file, File name: " + a.name + ", File size: " + a.size + ", Message: " + b);
          break;
        case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
          c.setStatus("\u041d\u0435\u0432\u0435\u0440\u043d\u044b\u0439 \u0442\u0438\u043f \u0444\u0430\u0439\u043b\u0430.");
          this.debug("Error Code: Invalid File Type, File name: " + a.name + ", File size: " + a.size + ", Message: " + b);
          break;
        default:
          a !== null && c.setStatus("\u041d\u0435\u0438\u0437\u0432\u0435\u0441\u0442\u043d\u0430\u044f \u043e\u0448\u0438\u0431\u043a\u0430");
          this.debug("Error Code: " + d + ", File name: " + a.name + ", File size: " + a.size + ", Message: " + b);
          break
      }
    }
  }catch(e) {
    this.debug(e)
  }
}
function fileDialogComplete(a, d) {
  try {
    if(a > 0) {
      document.getElementById(this.customSettings.cancelButtonId).disabled = false
    }
    elements = document.getElementById("form").elements;
    for(i = 0;i < elements.length;i++) {
      if(name = elements[i].name) {
        value = "";
        switch(elements[i].type) {
          case "select":
            value = elements[i].options[elements[i].selectedIndex].value;
            break;
          case "radio":
          ;
          case "checkbox":
            value = elements[i].checked ? 1 : 0;
            break;
          default:
            value = elements[i].value;
            break
        }
        this.addPostParam(name, value)
      }
    }
    this.startUpload()
  }catch(b) {
    this.debug(b)
  }
}
function uploadStart(a) {
  try {
    var d = new FileProgress(a, this.customSettings.progressTarget);
    d.setStatus("\u0417\u0430\u0433\u0440\u0443\u0437\u043a\u0430...");
    d.toggleCancel(true, this)
  }catch(b) {
  }
  return true
}
function uploadProgress(a, d, b) {
  try {
    var c = Math.ceil(d / b * 100), e = new FileProgress(a, this.customSettings.progressTarget);
    e.setProgress(c);
    e.setStatus("\u0417\u0430\u0433\u0440\u0443\u0437\u043a\u0430...")
  }catch(f) {
    this.debug(f)
  }
}
function uploadSuccess(a, d) {
  try {
    var b = new FileProgress(a, this.customSettings.progressTarget);
    b.setComplete();
	var reports = eval( '('+d+')' );

	if ( reports.error ) {
		b.setError();
		b.setStatus(reports.error);
	} else {

    	b.setStatus("\u0417\u0430\u0432\u0435\u0440\u0448\u0435\u043d\u043e.");

		if ( reports.success ) {
			totaluploadedfiles ++;
			var returnbox = reports.returnbox;

			returnbox = returnbox.replace(/&lt;/g, "<");
			returnbox = returnbox.replace(/&gt;/g, ">");
			returnbox = returnbox.replace(/&amp;/g, "&");

			$('#cont1').append( returnbox );

		}

	}

    b.toggleCancel(false)
  }catch(c) {
    this.debug(c)
  }
}
function uploadError(a, d, b) {
  try {
    var c = new FileProgress(a, this.customSettings.progressTarget);
    c.setError();
    c.toggleCancel(false);
    switch(d) {
      case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
        c.setStatus("\u041e\u0448\u0438\u0431\u043a\u0430: " + b);
        this.debug("Error Code: HTTP Error, File name: " + a.name + ", Message: " + b);
        break;
      case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
        c.setStatus("\u041e\u0448\u0438\u0431\u043a\u0430 \u0437\u0430\u0433\u0440\u0443\u0437\u043a\u0438.");
        this.debug("Error Code: Upload Failed, File name: " + a.name + ", File size: " + a.size + ", Message: " + b);
        break;
      case SWFUpload.UPLOAD_ERROR.IO_ERROR:
        c.setStatus("Server (IO) Error");
        this.debug("Error Code: IO Error, File name: " + a.name + ", Message: " + b);
        break;
      case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
        c.setStatus("\u041e\u0448\u0438\u0431\u043a\u0430 \u0431\u0435\u0437\u043e\u043f\u0430\u0441\u043d\u043e\u0441\u0442\u0438");
        this.debug("Error Code: Security Error, File name: " + a.name + ", Message: " + b);
        break;
      case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
        c.setStatus("\u041f\u0440\u0435\u0432\u044b\u0448\u0435\u043d \u043b\u0438\u043c\u0438\u0442 \u0437\u0430\u0433\u0440\u0443\u0437\u043a\u0438.");
        this.debug("Error Code: Upload Limit Exceeded, File name: " + a.name + ", File size: " + a.size + ", Message: " + b);
        break;
      case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
        c.setStatus("\u041e\u0448\u0438\u0431\u043a\u0430 \u0438\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0446\u0438\u0438.  \u0417\u0430\u0433\u0440\u0443\u0437\u043a\u0430 \u043f\u0440\u043e\u043f\u0443\u0449\u0435\u043d\u0430.");
        this.debug("Error Code: File Validation Failed, File name: " + a.name + ", File size: " + a.size + ", Message: " + b);
        break;
      case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
        if(this.getStats().files_queued === 0) {
          document.getElementById(this.customSettings.cancelButtonId).disabled = true
        }
        c.setStatus("\u041e\u0442\u043c\u0435\u043d\u0435\u043d\u043e");
        c.setCancelled();
        break;
      case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
        c.setStatus("\u041e\u0441\u0442\u0430\u043d\u043e\u0432\u043b\u0435\u043d\u043e");
        break;
      default:
        c.setStatus("\u041d\u0435\u0438\u0437\u0432\u0435\u0441\u0442\u043d\u0430\u044f \u043e\u0448\u0438\u0431\u043a\u0430: " + d);
        this.debug("Error Code: " + d + ", File name: " + a.name + ", File size: " + a.size + ", Message: " + b);
        break
    }
  }catch(e) {
    this.debug(e)
  }
}
function uploadComplete(a) {
  if(this.getStats().files_queued === 0) {
    document.getElementById(this.customSettings.cancelButtonId).disabled = true
  }
}
function queueComplete(a) {
    if ( totaluploadedfiles > 0 ) tabClick(0);
	totaluploadedfiles = 0;
}
;