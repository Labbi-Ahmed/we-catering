<?php
/**
 * Menu Item Modal Template
 *
 * @package WeCatering
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="add-menu-item-modal" class="we-catering-modal" style="display: none;">
	<div class="we-catering-modal-content">
		<div class="we-catering-modal-header">
			<h3><?php esc_html_e( 'Add New Menu Item', 'we-catering' ); ?></h3>
			<span class="we-catering-modal-close">&times;</span>
		</div>
		<div class="we-catering-modal-body">
			<form id="add-menu-item-form">
				<input type="hidden" id="menu_item_id" name="menu_item_id" value="" />
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="menu_item_name"><?php esc_html_e( 'Name', 'we-catering' ); ?></label>
						</th>
						<td>
							<input type="text" id="menu_item_name" name="name" class="regular-text" required />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="menu_item_description"><?php esc_html_e( 'Description', 'we-catering' ); ?></label>
						</th>
						<td>
							<textarea id="menu_item_description" name="description" class="regular-text" rows="3" placeholder="<?php esc_attr_e( 'Enter menu item description...', 'we-catering' ); ?>"></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="menu_item_items"><?php esc_html_e( 'Items', 'we-catering' ); ?></label>
						</th>
						<td>
							<select id="menu_item_items" name="items" class="regular-text we-catering-select2" style="width: 100%;" multiple>
								<option value=""><?php esc_html_e( 'Select or add items...', 'we-catering' ); ?></option>

								<!-- Proteins -->
								<optgroup label="<?php esc_attr_e( 'Proteins', 'we-catering' ); ?>">
									<option value="Chicken" data-content="Grilled or roasted chicken, rich in protein."><?php esc_html_e( 'Chicken', 'we-catering' ); ?></option>
									<option value="Beef" data-content="Tender beef dishes, high in iron and protein."><?php esc_html_e( 'Beef', 'we-catering' ); ?></option>
									<option value="Fish" data-content="Fresh fish, rich in omega-3 fatty acids."><?php esc_html_e( 'Fish', 'we-catering' ); ?></option>
									<option value="Lamb" data-content="Juicy lamb, served roasted or stewed."><?php esc_html_e( 'Lamb', 'we-catering' ); ?></option>
									<option value="Eggs" data-content="Boiled, scrambled, or fried eggs."><?php esc_html_e( 'Eggs', 'we-catering' ); ?></option>
									<option value="Tofu" data-content="Plant-based protein, cooked in various styles."><?php esc_html_e( 'Tofu', 'we-catering' ); ?></option>
								</optgroup>

								<!-- Grains & Starches -->
								<optgroup label="<?php esc_attr_e( 'Grains & Starches', 'we-catering' ); ?>">
									<option value="Rice" data-content="Steamed or fried rice, a staple food."><?php esc_html_e( 'Rice', 'we-catering' ); ?></option>
									<option value="Bread" data-content="Freshly baked bread, soft and fluffy."><?php esc_html_e( 'Bread', 'we-catering' ); ?></option>
									<option value="Pasta" data-content="Italian pasta with sauce options."><?php esc_html_e( 'Pasta', 'we-catering' ); ?></option>
									<option value="Potatoes" data-content="Mashed, fried, or baked potatoes."><?php esc_html_e( 'Potatoes', 'we-catering' ); ?></option>
									<option value="Quinoa" data-content="Healthy grain alternative, rich in protein."><?php esc_html_e( 'Quinoa', 'we-catering' ); ?></option>
								</optgroup>

								<!-- Vegetables -->
								<optgroup label="<?php esc_attr_e( 'Vegetables', 'we-catering' ); ?>">
									<option value="Mixed Vegetables" data-content="Seasonal mixed vegetables, lightly cooked."><?php esc_html_e( 'Mixed Vegetables', 'we-catering' ); ?></option>
									<option value="Salad" data-content="Fresh green salad with dressing."><?php esc_html_e( 'Salad', 'we-catering' ); ?></option>
									<option value="Broccoli" data-content="Steamed or stir-fried broccoli."><?php esc_html_e( 'Broccoli', 'we-catering' ); ?></option>
									<option value="Carrots" data-content="Steamed, roasted, or raw carrots."><?php esc_html_e( 'Carrots', 'we-catering' ); ?></option>
									<option value="Spinach" data-content="Cooked spinach, rich in iron."><?php esc_html_e( 'Spinach', 'we-catering' ); ?></option>
								</optgroup>

								<!-- Legumes -->
								<optgroup label="<?php esc_attr_e( 'Legumes', 'we-catering' ); ?>">
									<option value="Lentils" data-content="Lentil curry or soup, high in protein."><?php esc_html_e( 'Lentils', 'we-catering' ); ?></option>
									<option value="Chickpeas" data-content="Cooked chickpeas or hummus."><?php esc_html_e( 'Chickpeas', 'we-catering' ); ?></option>
									<option value="Beans" data-content="Variety of beans cooked with spices."><?php esc_html_e( 'Beans', 'we-catering' ); ?></option>
								</optgroup>

								<!-- Soups & Starters -->
								<optgroup label="<?php esc_attr_e( 'Soups & Starters', 'we-catering' ); ?>">
									<option value="Soup" data-content="Warm soups with seasonal flavors."><?php esc_html_e( 'Soup', 'we-catering' ); ?></option>
									<option value="Appetizer" data-content="Light starters before the main course."><?php esc_html_e( 'Appetizer', 'we-catering' ); ?></option>
								</optgroup>

								<!-- Desserts & Beverages -->
								<optgroup label="<?php esc_attr_e( 'Desserts & Beverages', 'we-catering' ); ?>">
									<option value="Dessert" data-content="Sweet treats like cake, pudding, or ice cream."><?php esc_html_e( 'Dessert', 'we-catering' ); ?></option>
									<option value="Beverage" data-content="Soft drinks or refreshing juices."><?php esc_html_e( 'Beverage', 'we-catering' ); ?></option>
									<option value="Coffee" data-content="Freshly brewed hot coffee."><?php esc_html_e( 'Coffee', 'we-catering' ); ?></option>
									<option value="Tea" data-content="Hot black or green tea."><?php esc_html_e( 'Tea', 'we-catering' ); ?></option>
								</optgroup>
							</select>
							<p class="description"><?php esc_html_e( 'Select multiple items or type to add new ones', 'we-catering' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="menu_item_price"><?php esc_html_e( 'Price', 'we-catering' ); ?></label>
						</th>
						<td>
							<div class="we-catering-price-input">
								<span class="we-catering-currency"><?php esc_html_e( 'BDT', 'we-catering' ); ?></span>
								<input type="number" id="menu_item_price" name="price" class="regular-text" step="0.01" min="0" required />
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="menu_item_category"><?php esc_html_e( 'Category', 'we-catering' ); ?></label>
						</th>
						<td>
							<select id="menu_item_category" name="category" class="regular-text">
								<option value=""><?php esc_html_e( 'Select Category', 'we-catering' ); ?></option>
								<option value="breakfast"><?php esc_html_e( 'Breakfast', 'we-catering' ); ?></option>
								<option value="lunch"><?php esc_html_e( 'Lunch', 'we-catering' ); ?></option>
								<option value="dinner"><?php esc_html_e( 'Dinner', 'we-catering' ); ?></option>
								<option value="snacks"><?php esc_html_e( 'Snacks', 'we-catering' ); ?></option>
								<option value="beverages"><?php esc_html_e( 'Beverages', 'we-catering' ); ?></option>
							</select>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<div class="we-catering-modal-footer">
			<button type="button" class="button button-secondary we-catering-modal-close we-catering-cancel-button"><?php esc_html_e( 'Cancel', 'we-catering' ); ?></button>
			<button type="button" class="button button-primary" id="save-menu-item"><?php esc_html_e( 'Save Menu Item', 'we-catering' ); ?></button>
		</div>
	</div>
</div>
