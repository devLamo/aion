var textPadding = 16, strictDocType = true, tabView_maxNumberOfTabs = 6, dle_tabObj = [], activeTabIndex = [], MSIE = navigator.userAgent.indexOf("MSIE") >= 0 ? true : false, navigatorVersion = navigator.appVersion.replace(/.*?MSIE (\d\.\d).*/g, "$1") / 1, tabView_countTabs = [], tabViewHeight = [], tabDivCounter = 0;
function setPadding(a, f) {
  var c = a.getElementsByTagName("SPAN")[0];
  c.style.paddingLeft = f + "px";
  c.style.paddingRight = f + "px"
}
function showTab(a, f) {
  var c = a + "_";
  if(document.getElementById("tabView" + c + f)) {
    if(activeTabIndex[a] >= 0) {
      if(activeTabIndex[a] == f) {
        return
      }
      var b = document.getElementById("tabTab" + c + activeTabIndex[a]);
      b.className = "tabInactive";
      var d = b.getElementsByTagName("IMG")[0];
      d.src = "engine/skins/images/tr_inactive.gif";
      document.getElementById("tabView" + c + activeTabIndex[a]).style.display = "none"
    }
    b = document.getElementById("tabTab" + c + f);
    b.className = "tabActive";
    d = b.getElementsByTagName("IMG")[0];
    d.src = "engine/skins/images/tr_active.gif";
    document.getElementById("tabView" + c + f).style.display = "block";
    activeTabIndex[a] = f;
    c = b.parentNode.getElementsByTagName("DIV")[0];
    countObjects = 0;
    d = 2;
    for(var e = false;c;) {
      if(c.tagName == "DIV") {
        if(e) {
          e = false;
          d -= 2
        }
        if(c == b) {
          d -= 2;
          e = true;
          setPadding(c, textPadding + 1)
        }else {
          setPadding(c, textPadding)
        }
        c.style.left = d + "px";
        countObjects++;
        d += 2
      }
      c = c.nextSibling
    }
  }

}
function tabClick() {
  var a = this.id.split("_");
  var tabid = a[a.length - 1].replace(/[^0-9]/gi, "");
  showTab(this.parentNode.parentNode.id, tabid);

	if ( window.create_editor ) {
		if (navigator.userAgent.toLowerCase().indexOf('firefox/11.0') != -1 && tabid == 0) {
			window.setTimeout(function() {
				$('iframe').each(function(i)
			        {
			            $(this).height($(this).height()+1);
			        });
			}, 100);
		
		}
	}

}
function initTabs(a, f, c, b, d) {
  if(!d || d == "undefined") {
    dle_tabObj[a] = document.getElementById(a);
    b += "";
    if(b.indexOf("%") < 0) {
      b += "px"
    }
    dle_tabObj[a].style.width = b;
    d = document.createElement("DIV");
    b = dle_tabObj[a].getElementsByTagName("DIV")[0];
    dle_tabObj[a].insertBefore(d, b);
    d.className = "dle_tabPane";
    tabView_countTabs[a] = 0
  }else {
    d = dle_tabObj[a].getElementsByTagName("DIV")[0];
    dle_tabObj[a].getElementsByTagName("DIV");
    c = tabView_countTabs[a]
  }
  for(b = 0;b < f.length;b++) {
    var e = document.createElement("DIV");
    e.id = "tabTab" + a + "_" + (b + tabView_countTabs[a]);
    e.onclick = tabClick;
    e.className = "tabInactive";
    d.appendChild(e);
    var h = document.createElement("SPAN");
    h.innerHTML = f[b];
    e.appendChild(h);
    var g = document.createElement("IMG");
    g.valign = "bottom";
    g.src = "engine/skins/images/tr_inactive.gif";
    if(navigatorVersion && navigatorVersion < 6 || MSIE && !strictDocType) {
      g.style.styleFloat = "none";
      g.style.position = "relative";
      g.style.top = "4px";
      h.style.paddingTop = "4px";
      e.style.cursor = "hand"
    }
    e.appendChild(g)
  }
  d = dle_tabObj[a].getElementsByTagName("DIV");
  for(b = e = 0;b < d.length;b++) {
    if(d[b].className == "dle_aTab") {
      d[b].style.display = "none";
      d[b].id = "tabView" + a + "_" + e;
      e++
    }
  }
  tabView_countTabs[a] += f.length;
  showTab(a, c);
  return c
}
;