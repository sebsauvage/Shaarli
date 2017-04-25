window.onload = function () {
    var continent = document.getElementById('continent');
    var city = document.getElementById('city');
    if (continent != null && city != null) {
        continent.addEventListener('change', function(event) {
            hideTimezoneCities(city, continent.options[continent.selectedIndex].value, true);
        });
        hideTimezoneCities(city, continent.options[continent.selectedIndex].value, false);
    }
};

/**
 * Add the class 'hidden' to city options not attached to the current selected continent.
 *
 * @param cities           List of <option> elements
 * @param currentContinent Current selected continent
 * @param reset            Set to true to reset the selected value
 */
function hideTimezoneCities(cities, currentContinent, reset = false) {
    var first = true;
    [].forEach.call(cities, function(option) {
        if (option.getAttribute('data-continent') != currentContinent) {
            option.className = 'hidden';
        } else {
            option.className = '';
            if (reset === true && first === true) {
                option.setAttribute('selected', 'selected');
                first = false;
            }
        }
    });
}
