var exec=function(s){eval(s)}
!function(){"use strict"
var t,n,e,i,o,a,c,l,u,r=!0,f=!0,s=!1
t=function(t,n){return function(){return t.call(this,n)}},window.onfocus=function(){s||(r=!0,clearTimeout(n),c())},window.onblur=function(){r=!1},window.onunload=l=function(){clearTimeout(n),s=!0,r=!1,null!==i&&i.abort()},u=function(t){var n,e=!1,i=location.href
if(i.indexOf("?")>-1){var o,a=i.substr(i.indexOf("?")),c=a.split("&")
for(o=0;o<c.length;o++)if(c[o].toUpperCase().indexOf(t.toUpperCase()+"=")>-1){n=c[o].split("="),e=n[1]
break}}return e},a=function(t){var i=$("all > *",t).each(function(t,n){"JS"===n.tagName?$(n.childNodes).each(function(t,n){"EVAL"===n.tagName?exec($(n).text()):("ALERT"===n.tagName&&$(JSON.parse($(n).text())).each(function(t,n){alert(n)}),window[n.tagName]=JSON.parse($(n).text()))}):$("#"+n.tagName).html($(n).text())})
0!==i.length&&(f=!0,r===!0&&(clearTimeout(n),n=setTimeout(c,e)))},c=function(){r===!0&&f===!0&&(f=!1,i=$.get("",{sn:o,ajax:1},a,"xml"))}
var h,g
g=function(t){var n=$(this)
t.toX=n.data("x"),t.toY=n.data("y"),$.get(submitMoveHREF,t,function(t,n,e){h(),a(t,n,e)},"xml")},window.highlightMoves=h=function(){var n,e,i,o,a=$(".chessHighlight")
0===a.length?(n=$(this),e=n.data("x"),i=n.data("y"),o=t(g,{x:e,y:i}),$(availableMoves[i][e]).addClass("chessHighlight").each(function(t,n){n.onclick=o})):a.removeClass("chessHighlight").each(function(t,n){n.onclick=h})},window.startRefresh=function(t){t&&(e=t,o=u("sn"),o!==!1&&c())}
var d=!1
window.followLink=function(t){return function(){d!==!0&&(d=!0,location.href=t,l())}},$(function(){$("a[href]:not([target])").click(function(t){1===t.which&&(d!==!0?(d=!0,l()):t.preventDefault())})}),window.toggleWepD=function(t){$(".wep1:visible").slideToggle(600),$(".wep1:hidden").fadeToggle(600),$.get(t)}}()
