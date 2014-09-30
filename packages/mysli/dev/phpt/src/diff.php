<?php

namespace mysli\dev\phpt {
    class diff {
        /**
         * Generate diff
         * @param  string  $expect
         * @param  string  $expect_raw
         * @param  string  $output
         * @param  boolean $regex
         * @return array
         */
        static function generate($expect, $expect_raw, $output, $regex) {
            $expect     = explode("\n", $expect);
            $expect_raw = explode("\n", $expect_raw);
            $output     = explode("\n", $output);
            $diff = self::generate_array($expect, $expect_raw, $output, $regex);
            return $diff;
        }

        /**
         * Compare indidual line.
         * @param  string  $l1
         * @param  string  $l2
         * @param  boolean $regex
         * @return boolean
         */
        private static function compare_line($l1, $l2, $regex) {
            if ($regex) {
                return preg_match('/^'. $l1 . '$/s', $l2);
            } else {
                return !strcmp($l1, $l2);
            }
        }
        /**
         * @param  array   $ar1
         * @param  array   $ar2
         * @param  boolean $regex
         * @param  integer $idx1
         * @param  integer $idx2
         * @param  integer $cnt1
         * @param  integer $cnt2
         * @param  integer $steps
         * @return integer
         */
        private static function count_array(array $ar1, array $ar2, $regex,
                                            $idx1, $idx2,
                                            $cnt1, $cnt2, $steps) {
            $equal = 0;

            while ($idx1 < $cnt1 && $idx2 < $cnt2 &&
                   self::compare_line($ar1[$idx1], $ar2[$idx2], $regex)) {
                $idx1++;
                $idx2++;
                $equal++;
                $steps--;
            }

            if (--$steps > 0) {
                $eq1 = 0;
                $st = $steps / 2;

                for ($ofs1 = $idx1 + 1; $ofs1 < $cnt1 && $st-- > 0; $ofs1++) {
                    $eq = self::count_array($ar1, $ar2, $regex, $ofs1, $idx2,
                                            $cnt1, $cnt2, $st);
                    if ($eq > $eq1) {
                        $eq1 = $eq;
                    }
                }

                $eq2 = 0;
                $st = $steps;

                for ($ofs2 = $idx2 + 1; $ofs2 < $cnt2 && $st-- > 0; $ofs2++) {
                    $eq = self::count_array($ar1, $ar2, $regex, $idx1, $ofs2,
                                            $cnt1, $cnt2, $st);
                    if ($eq > $eq2) {
                        $eq2 = $eq;
                    }
                }

                if ($eq1 > $eq2) {
                    $equal += $eq1;
                } elseif ($eq2 > 0) {
                    $equal += $eq2;
                }
            }

            return $equal;
        }
        /**
         * Generate diff array
         * @param  array   $expect
         * @param  array   $expect_raw
         * @param  array   $output
         * @param  boolean $regex
         * @return array
         */
        private static function generate_array(array $expect, array $expect_raw,
                                               array $output, $regex) {
            $idx1 = 0; $ofs1 = 0; $cnt1 = count($expect);
            $idx2 = 0; $ofs2 = 0; $cnt2 = count($output);
            $diff = [];
            $old1 = [];
            $old2 = [];

            while ($idx1 < $cnt1 && $idx2 < $cnt2) {
                if (self::compare_line($expect[$idx1], $output[$idx2],
                                       $regex)) {
                    $idx1++;
                    $idx2++;
                    continue;
                } else {
                    $c1 = self::count_array($expect, $output, $regex, $idx1+1,
                                            $idx2, $cnt1, $cnt2, 10);
                    $c2 = self::count_array($expect, $output, $regex, $idx1,
                                            $idx2+1, $cnt1,  $cnt2, 10);
                    $before = isset($output[$idx2-1]) ? $output[$idx2-1] : null;
                    $after  = isset($output[$idx2+1]) ? $output[$idx2+1] : null;
                    if ($c1 > $c2) {
                        $old1[$idx1] = array(
                            $idx1, '-', $before, $expect_raw[$idx1++], $after);
                        $last = 1;
                    } elseif ($c2 > 0) {
                        $old2[$idx2] = array(
                            $idx2, '+', $before, $output[$idx2++], $after);
                        $last = 2;
                    } else {
                        $old1[$idx1] = array(
                            $idx1, '-', $before, $expect_raw[$idx1++], $after);
                        $old2[$idx2] = array(
                            $idx2, '+', $before, $output[$idx2++], $after);
                    }
                }
            }

            reset($old1); $k1 = key($old1); $l1 = -2;
            reset($old2); $k2 = key($old2); $l2 = -2;

            while ($k1 !== null || $k2 !== null) {
                if ($k1 == $l1 + 1 || $k2 === null) {
                    $l1 = $k1;
                    $diff[] = current($old1);
                    $k1 = next($old1) ? key($old1) : null;
                } elseif ($k2 == $l2 + 1 || $k1 === null) {
                    $l2 = $k2;
                    $diff[] = current($old2);
                    $k2 = next($old2) ? key($old2) : null;
                } elseif ($k1 < $k2) {
                    $l1 = $k1;
                    $diff[] = current($old1);
                    $k1 = next($old1) ? key($old1) : null;
                } else {
                    $l2 = $k2;
                    $diff[] = current($old2);
                    $k2 = next($old2) ? key($old2) : null;
                }
            }

            while ($idx1 < $cnt1) {
                $before = isset($output[$idx2-1]) ? $output[$idx2-1] : null;
                $after  = isset($output[$idx2+1]) ? $output[$idx2+1] : null;
                $diff[] = array($idx1, '-',  $before, $expect_raw[$idx1++], $after);
            }

            while ($idx2 < $cnt2) {
                $before = isset($output[$idx2-1]) ? $output[$idx2-1] : null;
                $after  = isset($output[$idx2+1]) ? $output[$idx2+1] : null;
                $diff[] = array($idx2, '+',  $before, $output[$idx2++], $after);
            }

            return $diff;
        }
    }
}
