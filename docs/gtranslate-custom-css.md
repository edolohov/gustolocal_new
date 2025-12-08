# CSS для поля Custom CSS плагина GTranslate

## 📋 Готовый CSS код

Скопируйте этот код в поле **Custom CSS** в настройках плагина GTranslate:

```css
/* GTranslate Widget - Защита от перекрытия бургер-меню */

/* На мобильных устройствах - сдвигаем виджет влево и ниже */
@media (max-width: 1024px) {
    .gt_float_switcher,
    .gtranslate_wrapper {
        left: 15px !important;
        right: auto !important;
        top: 70px !important; /* Ниже бургер-меню */
        z-index: 9997 !important; /* Ниже бургера (z-index: 1002) */
    }
}

/* На планшетах и мобильных - дополнительная защита */
@media (max-width: 768px) {
    .gt_float_switcher,
    .gtranslate_wrapper {
        left: 10px !important;
        top: 75px !important; /* Еще ниже для безопасности */
        padding: 6px !important;
    }
    
    /* Уменьшаем размер флажков на мобильных */
    .gt_float_switcher img,
    .gtranslate_wrapper img {
        width: 22px !important;
        height: 16px !important;
        margin: 0 3px !important;
    }
}

/* Очень маленькие экраны - минимизируем виджет */
@media (max-width: 480px) {
    .gt_float_switcher,
    .gtranslate_wrapper {
        left: 8px !important;
        top: 80px !important;
        padding: 4px !important;
        border-radius: 8px !important;
    }
    
    .gt_float_switcher img,
    .gtranslate_wrapper img {
        width: 20px !important;
        height: 14px !important;
        margin: 0 2px !important;
    }
}

/* Защита от перекрытия бургер-меню (если он справа) */
@media (max-width: 960px) {
    /* Если виджет справа - сдвигаем влево */
    .gt_float_switcher[style*="right"],
    .gtranslate_wrapper[style*="right"] {
        right: auto !important;
        left: 15px !important;
    }
}

/* Убеждаемся что виджет не перекрывает навигацию */
.gt_float_switcher,
.gtranslate_wrapper {
    pointer-events: auto !important;
}

/* Бургер-меню всегда выше виджета */
.gl-mobile-toggle,
.gl-navigation button[aria-label*="menu"],
.wp-block-navigation__responsive-container-open {
    z-index: 1002 !important;
    position: relative !important;
}

/* Виджет ниже бургера */
.gt_float_switcher,
.gtranslate_wrapper {
    z-index: 9997 !important;
}
```

## 🎯 Альтернативный вариант (виджет слева внизу на мобильных)

Если хотите разместить виджет в левом нижнем углу на мобильных:

```css
/* GTranslate Widget - Левое нижнее размещение на мобильных */

/* Десктоп - стандартное размещение */
.gt_float_switcher,
.gtranslate_wrapper {
    position: fixed !important;
    top: 20px !important;
    left: 20px !important;
    z-index: 9998 !important;
}

/* На мобильных - перемещаем в левый нижний угол */
@media (max-width: 768px) {
    .gt_float_switcher,
    .gtranslate_wrapper {
        top: auto !important;
        bottom: 20px !important;
        left: 15px !important;
        right: auto !important;
    }
}

/* Защита от перекрытия бургера */
@media (max-width: 960px) {
    .gt_float_switcher,
    .gtranslate_wrapper {
        z-index: 9997 !important; /* Ниже бургера */
    }
    
    .gl-mobile-toggle,
    .gl-navigation button {
        z-index: 1002 !important; /* Выше виджета */
    }
}
```

## 📱 Вариант 3: Компактный виджет справа (если бургер слева)

Если бургер-меню слева, а виджет справа:

```css
/* GTranslate Widget - Справа, не перекрывает бургер */

/* Десктоп */
.gt_float_switcher,
.gtranslate_wrapper {
    position: fixed !important;
    top: 20px !important;
    right: 20px !important;
    left: auto !important;
    z-index: 9998 !important;
}

/* На мобильных - сдвигаем ниже и уменьшаем */
@media (max-width: 768px) {
    .gt_float_switcher,
    .gtranslate_wrapper {
        top: 70px !important; /* Ниже бургера */
        right: 15px !important;
        padding: 5px !important;
    }
    
    .gt_float_switcher img,
    .gtranslate_wrapper img {
        width: 20px !important;
        height: 14px !important;
        margin: 0 2px !important;
    }
}

/* Защита z-index */
.gt_float_switcher,
.gtranslate_wrapper {
    z-index: 9997 !important; /* Ниже бургера */
}

.gl-mobile-toggle,
.gl-navigation button {
    z-index: 1002 !important; /* Выше виджета */
}
```

## 🔧 Как использовать

1. Откройте WordPress Admin → GTranslate → Settings
2. Найдите поле **Custom CSS**
3. Скопируйте один из вариантов выше
4. Вставьте в поле Custom CSS
5. Сохраните изменения
6. Проверьте на мобильном устройстве

## 📝 Рекомендация

**Рекомендую использовать первый вариант** - он размещает виджет слева и ниже бургера, что гарантирует отсутствие перекрытия.

Если нужно изменить позицию, просто измените значения `top`, `left`, `right` в медиа-запросах.

