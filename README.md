# Extra Fields Shortcode Plugin

This repository contains a simple WordPress plugin that lets you register additional post meta fields and display them using a shortcode.

## Usage

1. Place the `extra-fields-shortcode` directory in your `wp-content/plugins` directory.
2. In your theme or another plugin, register fields with:

```php
efs_register_field( 'subtitle', 'Subtitle', 'post' );
```

3. Edit a post and fill in the new field in the **Extra Fields** meta box.
4. Display the field in content using the shortcode:

```
[efs_field name="subtitle"]
```

You can also specify a `post_id` parameter to display a field from another post.
