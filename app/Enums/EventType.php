<?php

namespace App\Enums;

enum EventType: string
{
    case RestaurantView = 'restaurant_view';
    case MenuView = 'menu_view';
    case MenuItemView = 'menu_item_view';
    case Favorite = 'favorite';
    case PhoneClick = 'phone_click';
    case WhatsappClick = 'whatsapp_click';
    case DirectionsClick = 'directions_click';
    case VisitVerified = 'visit_verified';
    case ReviewCreated = 'review_created';
    case CouponIssued = 'coupon_issued';
    case CouponUsed = 'coupon_used';
}
