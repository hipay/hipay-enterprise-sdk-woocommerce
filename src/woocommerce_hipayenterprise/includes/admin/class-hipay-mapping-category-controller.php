<?php

defined('ABSPATH') || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Mapping_Category_Controller extends Hipay_Mapping_Abstract
{
    /**
     * @var string
     */
    const ID_WC_CATEGORY = "idWcCategory";

    /**
     * @var string
     */
    const ID_HIPAY_CATEGORY = "idHipayCategory";

    /**
     * Hipay_Mapping_Category_Helper constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->postType = "hipay_mapping_cat";
    }

    /**
     * Handles output
     */
    public function output()
    {
        if (! empty($_POST)) {
            $this->saveMappingCategories();
        }

        Hipay_Helper::process_template(
            'admin-mapping-category-settings.php',
            'admin',
            array(
                'current_page' => 'hipay-mapping-category',
                'wcCategories' =>  Hipay_Helper_Mapping::getWcCategories(),
                'hipayCategories' => Hipay_Helper_Mapping::getHipayCategories(),
                'mappedCategories' => $this->getAllMappingCategories()
            )
        );
    }

    /**
     * Save mapping categories
     *
     * @return void
     */
    public function saveMappingCategories()
    {
        $this->logs->logInfos("# SaveMappingCategories ");
        try {
            $wcCategories = Hipay_Helper_Mapping::getWcCategories();
            foreach ($wcCategories as $category) {
                $idPost = $_POST['wc_map_' . $category->term_id];
                $hipayCategory = $_POST['hipay_map_' . $category->term_id];

                if (!empty($hipayCategory)) {
                    $mapping = array(
                        self::ID_WC_CATEGORY => $category->term_id,
                        self::ID_HIPAY_CATEGORY => $hipayCategory
                    );

                    if (isset($idPost) && !empty($idPost)) {
                        $this->logs->logInfos("# UpdateMappingCategory " . print_r($mapping, true));
                        $this->updateMapping($idPost, $mapping);
                    } else {
                        $this->logs->logInfos("# createMappingCategory " . print_r($mapping, true));
                        $this->createMapping($mapping);
                    }
                } else {
                    $this->logs->logInfos("# Mapping is empty " . $category->term_id);
                }
            }
            $this->logs->logInfos("# Mapping categories is saved");

            self::add_message("Your settings have been saved.");
        } catch (Exception $e) {
            $this->logs->logException($e);
            self::add_error(
                __("An error occured during while saving the mapping. ", "hipayenterprise")
            );
        }
    }

    /**
     * @return array
     */
    public function getDefaultArgs()
    {
        return array(
            self::ID_HIPAY_CATEGORY => '',
            self::ID_WC_CATEGORY => '',
        );
    }

    /**
     * @return array
     */
    public function getAllMappingCategories()
    {
        $posts = $this->getPosts();
        $mappings = array ();
        foreach ($posts as $post) {
            $mappingCategory = new Hipay_Mapping_Category($post);
            $mappings[$mappingCategory->idWcCategory] = array(
                "idPost" => $mappingCategory->id,
                self::ID_HIPAY_CATEGORY=> $mappingCategory->idHipayCategory
            );
        }
        return $mappings;
    }
}
