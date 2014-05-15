<div class="section">
    <div class="container default spaced" style="min-height:400px;">
    <?php mk_calendar(); ?>
    <?php mk_calendar(350, true); ?>
    <?php function mk_calendar($position, $disabled = false) { ?>
            <div class="popup point up <?php echo get_alt(); ?>" style="left:<?php echo $position; ?>px;">
                <table class="calendar data <?php echo get_alt(); ?>">
                    <caption>
                        <a href="#" class="left">&lt;</a>
                        <span>April 2014</span>
                        <a href="#" class="right">&gt;</a>
                    </caption>
                    <thead>
                        <tr>
                            <th>M</th>
                            <th>T</th>
                            <th>W</th>
                            <th>T</th>
                            <th>F</th>
                            <th>S</th>
                            <th>S</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?> fade">31</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">1</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">2</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">3</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">4</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">5</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">6</td>
                        </tr>
                        <tr>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">7</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">8</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">9</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">10</td>
                            <td>11</td>
                            <td>12</td>
                            <td>13</td>
                        </tr>
                        <tr>
                            <td>14</td>
                            <td>15</td>
                            <td class="selected">16</td>
                            <td>17</td>
                            <td>18</td>
                            <td>19</td>
                            <td>20</td>
                        </tr>
                        <tr>
                            <td>21</td>
                            <td>22</td>
                            <td>23</td>
                            <td>24</td>
                            <td>25</td>
                            <td class="today">26</td>
                            <td>27</td>
                        </tr>
                        <tr>
                            <td>28</td>
                            <td>29</td>
                            <td>30</td>
                            <td class="fade">1</td>
                            <td class="fade">2</td>
                            <td class="fade">3</td>
                            <td class="fade">4</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
    <?php echo alt_link(); ?>
</div>
<script>
function initCalendar() {}
var ready = setInterval(function () {
    if (!$) { return; }
    clearInterval(ready);
    initCalendar();
}, 1000);
</script>