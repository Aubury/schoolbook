/* DB Snow Flakes Scripts for Admin Panel */

/* WP Color Picker */

jQuery(document).ready(function($){
    $('.db-snow-color').wpColorPicker();
});

/* Speed Input Range */

let inputSpeedMobile = document.getElementById("db_snow_speed_mobile");
let inputSpeedTablet = document.getElementById("db_snow_speed_tablet");
let inputSpeed = document.getElementById("db_snow_speed");
let outputSpeedMobile = document.getElementById("db_snow_speed_value_mobile");
let outputSpeedTablet = document.getElementById("db_snow_speed_value_tablet");
let outputSpeed = document.getElementById("db_snow_speed_value");
let minSpeed = 0.1
let maxSpeed = 2

outputSpeedMobile.innerHTML = inputSpeedMobile.value;
outputSpeedTablet.innerHTML = inputSpeedTablet.value;
outputSpeed.innerHTML = inputSpeed.value;

inputSpeedMobile.addEventListener('input', function(){
    SpeedInputRange( inputSpeedMobile, outputSpeedMobile, minSpeed, maxSpeed )
});
inputSpeedTablet.addEventListener('input', function(){
    SpeedInputRange( inputSpeedTablet, outputSpeedTablet, minSpeed, maxSpeed )
});
inputSpeed.addEventListener('input', function(){
    SpeedInputRange( inputSpeed, outputSpeed, minSpeed, maxSpeed )
});
window.addEventListener('load', function(){
    SpeedInputRange( inputSpeedMobile, outputSpeedMobile, minSpeed, maxSpeed )
    SpeedInputRange( inputSpeedTablet, outputSpeedTablet, minSpeed, maxSpeed )
    SpeedInputRange( inputSpeed, outputSpeed, minSpeed, maxSpeed )
});

function SpeedInputRange( input, output, min, max ) {
    let speed = input.value
    let width = output.offsetWidth
    let range = ( speed - min ) / ( max - min )
    let margin = ( range - 0.5 ) * ( width - 62 ) - 22

    output.innerHTML = speed;
    output.style.marginLeft = "calc(" + range * 100 + "% + " + margin + "px)";
}

/* Opacity Input Range */

let inputOpacityMobile  = document.getElementById("db_snow_opacity_mobile");
let inputOpacityTablet  = document.getElementById("db_snow_opacity_tablet");
let inputOpacity        = document.getElementById("db_snow_opacity");
let outputOpacityMobile = document.getElementById("db_snow_opacity_value_mobile");
let outputOpacityTablet = document.getElementById("db_snow_opacity_value_tablet");
let outputOpacity       = document.getElementById("db_snow_opacity_value");
let minOpacity = 0
let maxOpacity = 1

outputOpacityMobile.innerHTML = inputOpacityMobile.value;
outputOpacityTablet.innerHTML = inputOpacityTablet.value;
outputOpacity.innerHTML       = inputOpacity.value;

inputOpacityMobile.addEventListener('input', function(){
    OpacityInputRange( inputOpacityMobile, outputOpacityMobile, minOpacity, maxOpacity )
});
inputOpacityTablet.addEventListener('input', function(){
    OpacityInputRange( inputOpacityTablet, outputOpacityTablet, minOpacity, maxOpacity )
});
inputOpacity.addEventListener('input', function(){
    OpacityInputRange( inputOpacity, outputOpacity, minOpacity, maxOpacity )
});
window.addEventListener('load', function(){
    OpacityInputRange( inputOpacityMobile, outputOpacityMobile, minOpacity, maxOpacity )
    OpacityInputRange( inputOpacityTablet, outputOpacityTablet, minOpacity, maxOpacity )
    OpacityInputRange( inputOpacity, outputOpacity, minOpacity, maxOpacity )
});

function OpacityInputRange( input, output, min, max ) {
    let opacity = input.value
    let width = output.offsetWidth
    let range = ( opacity - min ) / ( max - min )
    let margin = ( range - 0.5 ) * ( width - 62 ) - 22

    output.innerHTML = opacity;
    output.style.marginLeft = "calc(" + range * 100 + "% + " + margin + "px)";
}