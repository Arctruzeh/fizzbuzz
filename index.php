<?php
/**
 * if a number is divisible by 3, echo fizz
 * if a number is divisible by 5, echo buzz
 * if a number is divisible by 3 and 5, echo fizzbuzz
 */

for ($x = 1; $x<=100; $x++) {
    $y = '';
    if ($x % 3 == 0) { 
        $y .= 'Fizz';
    }
    if ($x % 5 == 0) {
        $y .= 'Buzz';
    }
    if ($y == '') {
        $y = $x;
    }
    echo $y . '<br>';
}
echo '<script data-slug="fizzbuzz">
(function() {
  var slug = document.currentScript.getAttribute("data-slug");
  var link = document.createElement("a");
  link.href = "https://ajlato.com/projects/" + slug;
  link.innerHTML = "← Back to Portfolio";
  link.style.cssText = "position: fixed; bottom: 20px; right: 20px; background: #222; color: #fff; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-family: system-ui, sans-serif; font-size: 14px; z-index: 10000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: transform 0.2s;";
  link.onmouseover = function() { link.style.transform = "scale(1.05)"; };
  link.onmouseout = function() { link.style.transform = "scale(1)"; };
  document.body.appendChild(link);
})();
</script>';