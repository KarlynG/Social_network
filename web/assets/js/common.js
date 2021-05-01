$(document).ready(function(){
   if ($(".label-notifications").text() == 0){
       $(".label-notifications").text(" Notifications");
   }else{
   }
   
   notification();
   
   $(".btn-event").unbind('click').click(function () {
        errorAlert();
    });
    
    $(".btn-help").unbind('click').click(function () {
        errorAlert();
    });
   
   setInterval(function(){
       notification();
   }, 60000);
   
});

function errorAlert() {
    Swal.fire({
        icon: 'error',
        title: 'Ha ocurrido un problema',
        text: 'Esta opción aún está en desarrollo'
    })
}

function notification(){
    $.ajax({
       url: URL + '/notifications/get',
       type: 'GET',
       success: function (response) {
           var noti = " Notifications (" + response + ")";
           $(".label-notifications").html(noti);
           
           if(response == 0){
               $(".label-notifications").text(" Notifications");
           }else{
           }
       }
    });
}