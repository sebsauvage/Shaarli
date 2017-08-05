## Add the sharing button (_bookmarklet_) to your browser

- Open your Shaarli and `Login`
- Click the `Tools` button in the top bar
- Drag the **`✚Shaare link` button**, and drop it to your browser's bookmarks bar.

_This bookmarklet button is compatible with Firefox, Opera, Chrome and Safari. Under Opera, you can't drag'n drop the button: You have to right-click on it and add a bookmark to your personal toolbar._

![](images/bookmarklet.png)

## Share links using the _bookmarklet_

- When you are visiting a webpage you would like to share with Shaarli, click the _bookmarklet_ you just added.
- A window opens.
    - You can freely edit title, description, tags... to find it later using the text search or tag filtering.
    - You will be able to edit this link later using the ![](https://raw.githubusercontent.com/shaarli/Shaarli/master/images/edit_icon.png) edit button.
    - You can also check the “Private” box so that the link is saved but only visible to you. 
- Click `Save`.**Voilà! Your link is now shared.**

## Troubleshooting: The bookmarklet doesn't work with a few websites (e.g. Github.com)

Websites which enforce Content Security Policy (CSP), such as github.com, disallow usage of bookmarklets. Unfortunatly, there is nothing Shaarli can do about it.

See [#196](https://github.com/shaarli/Shaarli#196).

There is an open bug for both Firefox and Chromium:

- https://bugzilla.mozilla.org/show_bug.cgi?id=866522
- https://code.google.com/p/chromium/issues/detail?id=233903
