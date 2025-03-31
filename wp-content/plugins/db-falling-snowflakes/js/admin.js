/* DB Snow Flakes Scripts for Admin Panel */

/* WP Color Picker */

jQuery(document).ready(function($){
    $('.db-snow-color').wpColorPicker();
});

/* Speed Input Range */

let inputSpeed = document.getElementById("db_snow_speed");
let outputSpeed = document.getElementById("db_snow_speed_value");
let minSpeed = 0.1
let maxSpeed = 2

outputSpeed.innerHTML = inputSpeed.value;

inputSpeed.addEventListener('input', function(){
    SpeedInputRange(minSpeed, maxSpeed)
});
window.addEventListener('load', function(){
    SpeedInputRange(minSpeed, maxSpeed)
});

function SpeedInputRange(min, max) {
    let speed = inputSpeed.value
    let width = outputSpeed.offsetWidth
    let range = ( speed - min ) / ( max - min )
    let margin = ( range - 0.5 ) * ( width - 62 ) - 22

    outputSpeed.innerHTML = speed;
    outputSpeed.style.marginLeft = "calc(" + range * 100 + "% + " + margin + "px)";
}

/* Opacity Input Range */

let inputOpacity = document.getElementById("db_snow_opacity");
let outputOpacity = document.getElementById("db_snow_opacity_value");
let minOpacity = 0
let maxOpacity = 1

outputOpacity.innerHTML = inputOpacity.value;

inputOpacity.addEventListener('input', function(){
    OpacityInputRange(minOpacity, maxOpacity)
});
window.addEventListener('load', function(){
    OpacityInputRange(minOpacity, maxOpacity)
});

function OpacityInputRange(min, max) {
    let opacity = inputOpacity.value
    let width = outputOpacity.offsetWidth
    let range = ( opacity - min ) / ( max - min )
    let margin = ( range - 0.5 ) * ( width - 62 ) - 22

    outputOpacity.innerHTML = opacity;
    outputOpacity.style.marginLeft = "calc(" + range * 100 + "% + " + margin + "px)";
}