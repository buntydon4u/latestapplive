<?php

function productExceptSelf($nums) {
    $n = count($nums);
    $result = array_fill(0, $n, 1);
    
    // Compute prefix products
    $prefix = 1;
    for ($i = 0; $i < $n; $i++) {
        print_r($result[$i]);
        $result[$i] = $prefix;
        $prefix *= $nums[$i];
    }
    print_r($result); die;
    // Compute suffix products and multiply with prefix products
    $suffix = 1;
    for ($i = $n - 1; $i >= 0; $i--) {
        $result[$i] *= $suffix;
        $suffix *= $nums[$i];
    }

    return $result;
}

// Example usage
$nums = [1, 2, 3, 4,0,0,0];
$result = productExceptSelf($nums);

echo "Product array: " . implode(" ", $result) . "\n";

?>
