/*** Editor Script Wrapper ***/
var oScripts=document.getElementsByTagName("script"); 
var sEditorPath;
for(var i=0;i<oScripts.length;i++)
  {
  var sSrc=oScripts[i].src.toLowerCase();
  if(sSrc.indexOf("scripts/innovaeditor.js")!=-1) sEditorPath=oScripts[i].src.replace(/innovaeditor.js/,"");
}

document.write("<scr" + "ipt src='" + sEditorPath + "common/nlslightbox/nlslightbox.js' type='text/javascript'></scr" + "ipt>");
document.write("<scr" + "ipt src='" + sEditorPath + "common/nlslightbox/nlsanimation.js' type='text/javascript'></scr" + "ipt>");
document.write("<link href='" + sEditorPath + "common/nlslightbox/nlslightbox.css' rel='stylesheet' type='text/css' />");
document.write("<scr" + "ipt src='" + sEditorPath + "common/nlslightbox/dialog.js' type='text/javascript'></scr" + "ipt>");

document.write("<li"+"nk rel='stylesheet' href='"+sEditorPath+"style/istoolbar.css' type='text/css' />");
document.write("<scr"+"ipt src='"+sEditorPath+"istoolbar.js'></scr"+"ipt>");

if(navigator.appName.indexOf('Microsoft')!=-1) {
  document.write("<scr"+"ipt src='"+sEditorPath+"editor.js'></scr"+"ipt>");
} else if(navigator.userAgent.indexOf('Safari')!=-1) {
  document.write("<scr"+"ipt src='"+sEditorPath+"saf/editor.js'></scr"+"ipt>");
} else if(navigator.userAgent.indexOf('Opera')!=-1) {
  document.write("<scr"+"ipt src='"+sEditorPath+"opera/editor.js'></scr"+"ipt>");
} else {
  document.write("<scr"+"ipt src='"+sEditorPath+"moz/editor.js'></scr"+"ipt>");
}

function DLEcustomTag(StartTag, EndTag) {
  var obj = oUtil.obj;
  var oEditor = oUtil.oEditor;
  var oSel;
  
   obj.saveForUndo();
	
  if(navigator.appName.indexOf("Microsoft") != -1) {
    if(!oEditor) {
      return
    }
    oEditor.focus();
    obj.setFocus();
    oSel = oEditor.document.selection.createRange();

	var sHTML = StartTag + oSel.htmlText + EndTag;
	sHTML = sHTML.replace(/[\n\t\r]/gi, "");
	
    if(oSel.parentElement) {
     oSel.pasteHTML(sHTML)
	 
    }else {
      oSel.item(0).outerHTML = sHTML
    }

  }else {
    oSel = oEditor.getSelection();
    var range = oSel.getRangeAt(0);
	
	var d = oEditor.document.createElement('div'); 
    d.appendChild(range.cloneContents());
	var selhtml = d.innerHTML
	var sHTML = StartTag + selhtml + EndTag;
    var docFrag = range.createContextualFragment(sHTML);
    var lastNode = docFrag.childNodes[docFrag.childNodes.length - 1];
	range.deleteContents();
    range.insertNode(docFrag);
    try {
      oEditor.document.designMode = "on";
    }catch(e) {
    }
    range = oEditor.document.createRange();
    range.setStart(lastNode, lastNode.nodeValue.length);
    range.setEnd(lastNode, lastNode.nodeValue.length);
    oSel = oEditor.getSelection();
    oSel.removeAllRanges();
    oSel.addRange(range);
  }
};

function submit_all_data() {
  var sContent;
  for(var i = 0;i < oUtil.arrEditor.length;i++) {
    var oEdit = eval(oUtil.arrEditor[i]);
      sContent = oEdit.getXHTMLBody();
	  document.getElementById(oEdit.idTextArea).value = sContent
  }
};