# Attachment Widget Styling

You can pass a custom style to the attachment widget by adding a `style` parameter when including the widget in your view. For example:

```blade
@include('widgets.attachments', ['object' => $object, 'style' => 'custom-style'])
```

The `style` parameter will then be used to include the corresponding sub-view in the <code>widgets/attachments/_custom-style.blade.php</code> file. Make sure to create a sub-view file that matches the style name you provide.

By default, if no style is provided, the widget will use the `_card` style.