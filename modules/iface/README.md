IFace module for BetaKiller platform
---

No more routes and controllers with boilerplate code!

Website URLs are usually used for:

1) rendering HTML from some kind of data or
2) processing webhooks fired by external services or
3) acting as an RPC endpoints or
4) requesting additional data via AJAX/etc.

Task [1] is done via IFace, task [2] is done via WebHook, and tasks [3] and [4] are processed via RPC-like API.

`IFace` is a `V` from `MVC` plus PHP code and data required for rendering.
It defines required objects in constructor (they would be injected via DIC) and implements `getData()` method (it returns data prepared for view).

`WebHook` is a simplified `C` from `MVC` which is intended to handle external service` event.

`IFace` and `WebHook` have dedicated models which are implementing `UrlElementInterface`.
All of them are organized into the `UrlElementTree`. Models can be defined in the database or in `/config/ifaces.xml` files.

Each `UrlElement` URI can be:

1) static (representing single page like `/blog/` or `/contacts/`) or
2) dynamic (representing multiple pages like `/blog/{Post.id}/`)

Dynamic URL elements are mapped to an Entity and are using Entity's codename with a property name for URL parsing and generating.
They may represent a tree (like `/<parentCategory>/<childCategory>/`) and URL will be processed respectively.
Detected Entities are collected into `UrlContainerInterface` instance which can be injected into dedicated `IFace` or `WebHook` class.

URLs are not only parsed but can be constructed from any `UrlElement` by an `UrlElementHelper::makeUrl()` method.
All you need is IFace instance (or its model) and the set of Entities placed inside `UrlContainerInterface`.
Missing Entities will be fetched from the current HTTP request. They may be detected on the fly from Entity's relations but this is not recommended due to a performance leak.


Zones
---

All UrlElements are divided into zones (`Public`, `Admin`, `Preview`, etc).
They serve to organize `IFaces` and `WebHooks` into separated sets and to simplify URL generation.
Each zone has its own access restriction policies (public ifaces are allowed to anyone, admin ifaces are allowed only to users with `admin` role).


Entity/action binding
---

Each `UrlElement` can be linked to an Entity and a [CRUDLS](classes/BetaKiller/IFace/CrudlsActionsInterface.php) action.

Example:

- `AppPostItem` IFace has `Public` zone, `Post` Entity and `read` action
- `AdminPostItem` IFace has `Admin` zone, `Post` Entity and `read` action

Now you'll be able to generate URL for editing `Post` entity right from it's instance!

- public URL via `UrlElementHelper::getReadEntityUrl($entity, ZoneInterface::PUBLIC)`
- admin editing URL via `UrlElementHelper::getReadEntityUrl($entity, ZoneInterface::ADMIN)`

You may omit zone constant if you are creating a URL for current iface's zone. For different zone you need to pass it anyway.


ACL binding
---

Each UrlElement may be linked to a couple of ACL rules. Zone access restrictions are applied by default.
If Entity action is defined, then appropriate ACL resource and it's corresponding rule are used.
Additional custom ACL rules are applied also (if defined).
