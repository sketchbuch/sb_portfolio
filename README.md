# SB Portfolio (sb_portfolio)
A plugin for Wordpress that gives you a complete portfolio system with items, clients, testimonials, media support (WordPress media, hosted videos like youtube, flickr etc.), and more.

## Settings

### Links Tab

Here you can define link types for your site. Link types can be used in you theme to only output specific types of links.

## Portfolio Items

### Media

An item’s media can contain media from various sources and types. They can be arranged by drag and drop and can be grouped into types that you create in Portfolio Settings. You can then output media in your theme of a specific type. 

By default you can attach WordPress media files, videos from various sources or Flickr images, videos, or albums. SBP has hooks that you can use in your theme or plugins to extend the types of media. See the included addons as examples of how to add media types or add sources of videos. The Flickr media is an example of adding a media type and daily motion videos are an example of how to add new video sources.

To delete a media file click it once and it will be done semi transparent. When you save the item the deleted media will be removed.

### Links

You can add links to your portfolio records and as with media you can group them into different types. Click 'Add Link’ to create a new link. You can provide a title for the link, a description, and a tooltip. You can also decide if the link should have the relation=”nofollow” attribute to stop search engines from following the link. 

You also need to specify what the link actually links to. This can either be a URL, a Wordpress page or post, a portfolio record (item, client, or testimonial), or any other custom post type that you have setup in WordPress. In the future it will also be possible to extend the link URLs in a similar way to media.

The URL type of link also gives you the option to select an image - as all the other link types are records that could contain an image, you may want to add images to remote links to maintain your theme’s design.
