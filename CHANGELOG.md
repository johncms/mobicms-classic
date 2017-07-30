# Changelog
This list contains only most important changes.  
Detailed change in the [repository log](https://github.com/mobicms/mobicms-classic/commits).

## mobiCMS Classic 0.3.0  
`Release Date: 30.07.2017` 

### New
- Integrated [nikic/FastRoute](https://github.com/nikic/FastRoute) package
- Integrated [Plates](http://platesphp.com/) native template engine
- Integrated [mobicms/mobicms-error-handler](https://github.com/mobicms/mobicms-error-handler) package
- Added our own Asset Manager package
  
### Changed
- Old functionality of `head.php` and `end.php` has been removed
- The files `head.php` and `end.php` have been put into stub mode
- New algorithm for determining the user's location on the site
- All pictures are moved to the default theme
- Refactoring

### Fixed
- Nothing
  
### Deleted
- Temporarily removed theme selector
- Old function of fixing of location is removed
- Old function of fixing of IP history is removed
- Old advertizing module is removed
- Users Karma is removed


## mobiCMS Classic 0.2.0  
`Release Date: 14.06.2017` 

### New
- Integrated [Klein.php](https://github.com/klein/klein.php) package
- Implemented Routing, modules are refined and moved to the `/modules` folder
- Added Request and Response implemented through inheritance of Klein package 
- Added checkbox to manage the consolidation of forum posts
- New CAPTCHA, implemented by [mobicms-captcha](https://github.com/mobicms/mobicms-captcha) package
  
### Changed
- Global refactoring related to the implementation of Request and Response
- Global refactoring associated with deleting $kmess, $page, $start variables
- All images moved to the `/assets` folder and sorted by module
- The limit on the size of news is removed

### Fixed
- Unknown tag error in the Library module
  
### Deleted
- Global variables **$kmess**, **$page**, **$start** are deleted
- Ability to download forum topics
- Old password recovery function


## mobiCMS Classic 0.1.0  
`Release Date: 21.05.2017`  
This version based on a fork of [JohnCMS 7.1.0](https://github.com/john-cms/johncms-next) development branch from 14.05.2017.
The difference in a code consists in rebranding of system and some usefull optimizations.

### Changed
- Bootstrap optimization
- Optimization of the I18n system
- Optimization of the IP Ban system
- Rebranding and refactoring
