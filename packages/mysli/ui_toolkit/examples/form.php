<div class="section">
    <div class="container default <?php echo get_inverse(); ?>">
        <form class="<?php echo get_inverse(); ?>">
        <div class="row">
            <div class="column spaced x4">
                <div class="field">
                    <label for="ff_name">Name:</label>
                    <input type="text" id="ff_name" />
                </div>
                <div class="field">
                    <label for="ff_password">Password:</label>
                    <input type="password" id="ff_password" />
                </div>
                <div class="field">
                    <label for="ff_about">About:</label>
                    <textarea id="ff_about"></textarea>
                </div>
                <div class="field">
                    <label for="ff_fruit">Favorite Fruit:</label>
                    <select id="ff_fruit">
                        <option>Banana</option>
                        <option>Pear</option>
                        <option>Apple</option>
                        <option>Orange</option>
                        <option>Kiwi</option>
                        <option>Pineapple</option>
                        <option>Mango</option>
                    </select>
                </div>
                <div class="field">
                    <label for="ff_disabled">Disabled:</label>
                    <input type="text" disabled="true" id="ff_disabled" />
                </div>
                <div class="field">
                    <label for="ff_textarea_disabled">Disabled:</label>
                    <textarea id="ff_textarea_disabled" disabled="true"></textarea>
                </div>
                <div class="field">
                    <label for="ff_select_disabled">Disabled:</label>
                    <select id="ff_select_disabled" disabled="true">
                        <option>Banana</option>
                    </select>
                </div>
            </div>
            <div class="column spaced x4">
                <div class="field">
                    <label>Gender:</label>
                    <input type="radio" id="ff_gender_male" name="gender" />
                    <label for="ff_gender_male">Male</label>

                    <input type="radio" id="ff_gender_female" name="gender" />
                    <label for="ff_gender_female">Female</label>

                    <input type="radio" id="ff_gender_not" name="gender" />
                    <label for="ff_gender_not">I'd rather not say...</label>

                    <input type="radio" id="ff_gender_na" name="gender" disabled="true" />
                    <label for="ff_gender_na">Not available</label>
                </div>
                <div class="field">
                    <label>Your Pets:</label>
                    <input type="checkbox" id="ff_pet_cat" name="pet" />
                    <label for="ff_pet_cat">Cat</label>

                    <input type="checkbox" id="ff_pet_dog" name="pet" checked="true" />
                    <label for="ff_pet_dog">Dog</label>

                    <input type="checkbox" id="ff_pet_goat" name="pet" checked="true" />
                    <label for="ff_pet_goat">Goat</label>

                    <input type="checkbox" id="ff_pet_piglet" name="pet" />
                    <label for="ff_pet_piglet">Piglet</label>

                    <input type="checkbox" id="ff_pet_chicken" name="pet" />
                    <label for="ff_pet_chicken">Chicken</label>

                    <input type="checkbox" id="ff_pet_na" name="pet" disabled="true" />
                    <label for="ff_pet_na">Not available</label>
                </div>
                <div class="field">
                    <button type="button">Save</button>
                </div>
            </div>
        </div>
        </form>
    </div>
    <?php echo inverse_link(); ?>
</div>
