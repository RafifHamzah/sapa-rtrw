<?php

if (! function_exists('rupiah')) {
    /**
     * Format nominal integer Rupiah, mis. 50000 -> "Rp 50.000".
     */
    function rupiah(int|float|null $value): string
    {
        return 'Rp ' . number_format((int) $value, 0, ',', '.');
    }
}
