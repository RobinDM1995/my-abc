//
// function sotype(obj){
//   var that = obj;
//   if(document.getElementById(that.id).checked == true){
//     document.getElementById('trade').checked = false;
//     document.getElementById('refurbished').checked = false;
//     document.getElementById('new').checked = false;
//     document.getElementById(that.id).checked = true;
//   }
// }

window.onload = function(){

  //aanvaarden
  var btnaccept = document.getElementById('aanvaarden');
  var accept = document.querySelector('.accept');
  var change = document.querySelector('.feedback');

  if(btnaccept == null){
  }else{
    btnaccept.addEventListener('click', function(){
      accept.style.setProperty('display', 'inline');
      change.style.setProperty('display', 'none');
    });
  }
  //aanpassen
  var btnchange = document.getElementById('aanpassing');

  if(btnchange == null){
  }else{
    btnchange.addEventListener('click', function(){
      change.style.setProperty('display', 'inline');
      accept.style.setProperty('display', 'none');
    });
  }

  // ABC only one checkbox checked
  $('.check').click(function() {
    $('.check').not(this).prop('checked', false);
  });
}
