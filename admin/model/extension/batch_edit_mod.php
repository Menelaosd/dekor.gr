<?php
/**
 *
 * @author Clicker
 * Commercial Installation-Based License
 * This extension has Installation-based license is per OpenCart installation.
 * Support: info@clicker.com.ua
 * https://opencart.click
 *
 */


/**
 * Modifier class for developers
 * @author Clicker
 *
 */
class ModelExtensionBatchEditMod extends ModelExtensionBatchEdit {
	public function __construct($registry) {
		parent::__construct($registry);
	}

	/**
	 * Modify public variables before Ajax call
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::beforeExecuteAjax()
	 */
	public function beforeExecuteAjax(&$data) {

		/* You can modify public variables
		 *
		 * $this->filter_data
		 * $this->action
		 * $this->action_data
		 * $this->action_settings
		 * $this->allowed_option_types
		 * $this->customer_groups
		 * $this->languages
		 * $this->tax_rates
		 * $this->tax_classes
		 * $this->currencies
		 *
		 */

		return $data; // return false; - to abort default action. json can be filled in afterExecuteAjax()
	}

	/**
	 * Modify JSON before it is returned to Ajax call
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::afterExecuteAjax()
	 */
	public function afterExecuteAjax(&$json) {

		if (!empty($this->action) && $this->action == 'tab_action_attribute') {
			// fix attributes quotes after execution
			//$sql = "UPDATE `" . DB_PREFIX . "product_attribute` SET `text` = REPLACE(`text`, '\"', '&quot;') WHERE `text` LIKE '%\"%'";
			//$this->db->query($sql);
		}

		return $json;
	}

	/**
	 * Modify SQL request before it is executed
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::beforeGetProducts()
	 */
	public function beforeGetProducts(&$data, &$sql) {
		return $sql;
	}

	/**
	 * Modify request results set
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::afterGetProducts()
	 */
	public function afterGetProducts(&$data, &$query) {
		return $query;
	}

	/**
	 * Modify SQL request before it is executed
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::beforeGetTotalProducts()
	 */
	public function beforeGetTotalProducts(&$data, &$sql) {
		return $sql;
	}

	/**
	 * Modify request results set
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::afterGetTotalProducts()
	 */
	public function afterGetTotalProducts(&$data, &$query) {
		return $query;
	}

	/**
	 * Modify product text fields from different tables
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::afterProductFields()
	 */
	public function afterProductFields(&$text_fields) {
		return $text_fields;
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::afterCustomFields()
	 */
	public function afterCustomFields(&$custom_fields) {
		return $custom_fields;
	}

	/**
	 * Before rounding a price
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::beforePrettyPrice()
	 */
	public function beforePrettyPrice(&$price, &$params) {
		return $price;
	}

	/**
	 * After rounding a price
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::afterPrettyPrice()
	 */
	public function afterPrettyPrice(&$result, &$price, &$params) {
		return $result;
	}

	/**
	 * Before calculate field value/percent
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::beforeCalculateValue()
	 */
	public function beforeCalculateValue(&$old_value, &$new_value, &$sign, &$type) {
		return $old_value;
	}

	/**
	 * After calculate field value/percent
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::afterCalculateValue()
	 */
	public function afterCalculateValue(&$result, &$old_value, &$new_value, &$sign, &$type) {
		return $result;
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::beforeGetCustomerGroups()
	 */
	public function beforeGetCustomerGroups() {
		// You can set your variable $this->customer_groups
		return $this->customer_groups;
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::beforeGetCategories()
	 */
	public function beforeGetCategories($parent_id = 0) {
		return false;
	}

	/**
	 * Every action method can be modified
	 * {@inheritDoc}
	 * @see ModelExtensionBatchEdit::beforeAction()
	 */
	public function beforeAction($method, &$product_id, &$product_data, &$result) {
		// return false if no action modifications needed and standard function must be used
		// return 0 if modification needed and 0 products changed
		// return I number of products changed if modification needed and products were changed
		/* All request data is stored in prepared public variables
		 *
		 * $this->filter_data
		 * $this->action
		 * $this->action_data
		 * $this->action_settings
		 * $this->allowed_option_types
		 * $this->customer_groups
		 * $this->languages
		 * $this->tax_rates
		 * $this->tax_classes
		 * $this->currencies
		 *
		 * $method - is a parent function name (updateProducts, updateProductDescriptions, updateProductAttributes, updateProductCategories, updateProductDiscounts, updateProductFilters, updateProductLabels, updateProductLayouts, updateProductOptions, updateProductRelated, updateProductRewards, updateProductSeoUrl, updateProductSpecials, updateProductStores, etc.)
		 * $product_id
		 * $product_data
		 * $result - true/false - true if you have changed a product, is used in counter of modified products
		 *
		 */
		return false;
	}
}