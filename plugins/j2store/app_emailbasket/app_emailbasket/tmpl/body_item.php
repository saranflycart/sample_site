<?php
defined('_JEXEC') or die('Restricted access');
?>
<div class="table-responsive">
                                <table id="cart"
                                       class="adminlist table table-striped table-bordered table-hover ">
                                    <thead>
                                    <tr>
                                        <th style="text-align: left;"><?php echo JText::_( "J2STORE_CART_LINE_ITEM" ); ?></th>
                                        <th><?php echo JText::_('J2STORE_CART_LINE_ITEM_QUANTITY'); ?></th>
                                        <th><?php echo JText::_('J2STORE_CART_LINE_ITEM_TOTAL'); ?></th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    <?php $i=0; $k=0; $subtotal = 0;?>
                                    <?php foreach ($vars->items as $item) :
                                        $registry = new JRegistry;
                                        $registry->loadString($item->orderitem_params);
                                        $item->params = $registry;
                                        $thumb_image = $item->params->get('thumb_image', '');
                                        ?>

                                        <tr class="row<?php echo $k; ?>">
                                            <td><?php if($vars->params->get('show_thumb_cart', 1) && !empty($thumb_image)): ?>
                                                    <span class="cart-thumb-image"> <img
                                                            alt="<?php echo $item->orderitem_name; ?>"
                                                            src="<?php echo $thumb_image; ?>">
			</span> <br /> <?php endif; ?> <span class="cart-product-name"> <?php echo $item->orderitem_name; ?>
                                                    <?php if(!$vars->params->get('show_qty_field', 1)) : ?> <a
                                                        class="j2store-remove remove-icon"
                                                        href="<?php echo JRoute::_('index.php?option=com_j2store&view=carts&task=remove&cart_id='.$item->cart_id); ?>">X</a>
                                                    <?php endif; ?>
			</span> <br /> <?php if(isset($item->orderitemattributes) && $item->orderitemattributes): ?>
                                                    <span class="cart-item-options"> <?php foreach ($item->orderitemattributes as $attribute): ?>
                                                            <small> - <?php echo JText::_($attribute->orderitemattribute_name); ?>
                                                                : <?php echo $attribute->orderitemattribute_value; ?>
                                                            </small> <br /> <?php endforeach;?>
			</span> <?php endif; ?> <?php if($vars->params->get('show_price_field', 1)): ?>

                                                    <span class="cart-product-unit-price"> <span class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_UNIT_PRICE'); ?>
				</span> <span class="cart-item-value"> <?php echo $vars->currency->format($vars->order->get_formatted_lineitem_price($item, $vars->params->get('checkout_price_display_options', 1))); ?>
				</span>
			</span> <?php endif; ?> <?php if($vars->params->get('show_sku', 1)): ?>
                                                    <br /> <span class="cart-product-sku"> <span class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_SKU'); ?>
				</span> <span class="cart-item-value"><?php echo $item->orderitem_sku; ?>
				</span>
			</span> <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;" class="product_quantity_input"><?php $type = 'text'; ?>
                                                <?php echo $item->orderitem_quantity; ?> <!-- Keep Original quantity to check any update to it when going to checkout -->

                                            </td>
                                            <td class="cart-line-subtotal"><?php echo $vars->currency->format($vars->order->get_formatted_lineitem_total($item, $vars->params->get('checkout_price_display_options', 1))); ?>
                                            </td>
                                        </tr>
                                        <?php ++$i; $k = (1 - $k); ?>
                                    <?php endforeach; ?>
                                    </tbody>



                                    <tfoot class="j2store-cart-footer">
                                    <!-- subtotal -->
                                    <tr class="cart_subtotal">
                                        <td colspan="2" style="font-weight: bold; text-align: right;"><?php echo JText::_( "J2STORE_CART_SUBTOTAL" ); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php echo $vars->currency->format($vars->order->get_formatted_subtotal($vars->params->get('checkout_price_display_options', 1))); ?>
                                        </td>
                                    </tr>

                                    <!-- shipping -->
                                    <?php
                                    if(isset($vars->order->order_shipping) && !empty($vars->order->shipping->ordershipping_name)): ?>
                                        <tr>
                                            <td colspan="2" style="font-weight: bold; text-align: right;">
                                                <?php echo JText::_(stripslashes($vars->shipping->ordershipping_name)); ?>
                                            </td>
                                            <td><?php echo $vars->currency->format($vars->order->order_shipping); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <!-- shipping tax -->
                                    <?php if(isset($vars->order->order_shipping_tax) && $vars->order->order_shipping_tax > 0): ?>
                                        <tr>
                                            <td colspan="2" style="font-weight: bold; text-align: right;"><?php echo JText::_('J2STORE_ORDER_SHIPPING_TAX'); ?>
                                            </td>
                                            <td>
                                                <?php echo $vars->currency->format($vars->order->order_shipping_tax); ?> </td>
                                        </tr>
                                    <?php endif; ?>

                                    <!-- coupon -->
                                    <?php if(isset($vars->coupons)): ?>
                                        <?php foreach($vars->coupons as $coupon): ?>
                                            <tr>
                                                <td colspan="2" style="font-weight: bold; text-align: right;"><?php echo JText::sprintf('J2STORE_COUPON_TITLE', $coupon->coupon_code); ?>
                                                    <a class="j2store-remove remove-icon" href="javascript:void(0)"
                                                       onClick="jQuery('#j2store-cart-form #j2store-cart-task').val('removeCoupon'); jQuery('#j2store-cart-form').submit();">X
                                                    </a>
                                                </td>
                                                <td><?php echo $vars->currency->format($coupon->amount); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif;?>

                                    <?php if(isset($vars->taxes) && count($vars->taxes) ): ?>
                                        <tr>
                                            <td colspan="2" style="font-weight: bold; text-align: right;"><?php if($vars->params->get('checkout_price_display_options', 0) == 1): ?>
                                                    <?php echo JText::_('J2STORE_CART_INCLUDING_TAX'); ?> <?php else: ?>
                                                    <?php echo JText::_('J2STORE_CART_TAX'); ?> <?php endif; ?> <br /> <?php foreach ($vars->taxes as $tax): ?>
                                                    <?php echo JText::_($tax->ordertax_title); ?> (<?php echo (float) $tax->ordertax_percent; ?>%)<br />
                                                <?php endforeach; ?>
                                            </td>
                                            <td>
                                                <br />
                                                <?php foreach ($vars->taxes as $tax): ?>
                                                    <?php echo $vars->currency->format($tax->ordertax_amount); ?>
                                                    <br />
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <!-- total-->
                                    <tr>
                                        <td colspan="2" style="font-weight: bold; text-align: right;"><?php echo JText::_( "J2STORE_CART_GRANDTOTAL" ); ?>
                                        </td>
                                        <td style="text-align: right;"><?php if($vars->params->get('auto_calculate_tax', 1)):?>
                                                <?php echo $vars->currency->format($vars->order->order_total); ?> <?php else: ?>
                                                <?php echo $vars->currency->format($vars->order->order_total); ?> <?php //  echo J2StorePrices::number($vars->totals['total']);?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>

