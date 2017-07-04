var browserWidth = $(window).width();
var browserHeight = $(window).height();

var style = document.createElement('style');
style.type = 'text/css';
style.innerHTML = '#lightbox-container-image-box { max-width:'+(browserWidth-20)+'px;max-height:'+(browserHeight-120)+'px; }';
document.getElementsByTagName('head')[0].appendChild(style);

var style2 = document.createElement('style');
style2.innerHTML = '#lightbox-container-image-data-box { max-width:'+(browserWidth-40)+'px; }';
document.getElementsByTagName('head')[0].appendChild(style2);

var style3 = document.createElement('style');
style3.innerHTML = '#lightbox-container-image img { max-width:'+(browserWidth-40)+'px;max-height:'+(browserHeight-140)+'px; }';
document.getElementsByTagName('head')[0].appendChild(style3);
