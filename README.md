Colecta is a Symfony-based CMS for a group of people to share and link pieces of information.

Those pieces of information are called "Items", and they are composed of:
  - title
  - slug
  - summary
  - tagwords
  - date
  - category
  - comments
  - other items it is related to
  - author
  - other people with permission to modify it
  
From this schema grows many types of items extending its information.

The reason the amount of text is so limited on the item basis is for efficiency on the searching process. So all relevant information must be placed on "title", "summary" and "tagwords" fields.

