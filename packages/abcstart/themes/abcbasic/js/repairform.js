window.onload = function(){
  /* PROGRESSBAR */
      var slider = document.getElementById('progressSlider');
      var contactStep = document.getElementById('contact');
      var companyStep = document.getElementById('company');
      var itemStep = document.getElementById('item');
      var overviewStep = document.getElementById('overview');

        if($('#step1').is(':visible')){
          contactStep.setAttribute('class', 'active');
          slider.style.width = '25%';
        }

        if($('#step2').is(':visible')){
          contactStep.setAttribute('class', 'active');
          companyStep.setAttribute('class', 'active');
          slider.style.width = '50%';
        }

        if($('#step3').is(':visible')){
          contactStep.setAttribute('class', 'active');
          companyStep.setAttribute('class', 'active');
          itemStep.setAttribute('class', 'active');
          slider.style.width = '75%';
        }

        if($('#step4').is(':visible')){
          contactStep.setAttribute('class', 'active');
          companyStep.setAttribute('class', 'active');
          itemStep.setAttribute('class', 'active');
          overviewStep.setAttribute('class', 'active');
          slider.style.width = '100%';
        }
}
