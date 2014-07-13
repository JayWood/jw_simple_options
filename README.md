## Simple Options - A WordPress Options Framework
Thank you for taking time to download, or at least consider downloading, my WordPress Options Framework.  This options framework was designed out of both a necessity and a side-project of mine. I also noticed there weren't that many frameworks around at the time of writing this, and I just didn't see the point of paying money for a huge frameworks when all I usually need is a few options.

That's when this little puppy was born.  This wordpress options framework leverages the WordPress settings API, and takes the guess work out of how the options page should look.  All you have to do is drop in a configuration array to the class, and you're done.  Defaults are already built in if you want to only provide options data, though not recommended.

### Pull Requests
Please make a pull request to the **development branch** only.  All request to the `master` branch will be denied.

### Documentation
The code itself has been documented to the best of my ability within the necessary files.  However, my attempt at following PHP documentation standards may be less than some would come to expect, although I'm pretty sure most of you can understand the inline documentation.  An example configuration file should be enough to get you started, however, `not all config options are included in the file`.

### Currently Supported Field Types
* `Checkbox`
* `Timeframe`
* `Text Area`
* `Textbox`
* `Color`
* `Number`
* `Radio`
* `WP Editor` - with settings for media buttons & teeny
* `Media Upload`
* `Password`
* `Data Array` - Neat little thing to allow data matrices
* `Select`
* `Section` - Header tags to separate options.


### Licensing
This work is licensed under the GPLv2 License to be WordPress plugin compatible.  If you think this could use a little extra input, by all means make a pull request to the development branch.

### Support
* Need support for this library? [Open a ticket](https://github.com/JayWood/jw_simple_options/issues)
* Have a feature you think should be included?  Make a [pull request](https://github.com/JayWood/jw_simple_options/pulls) to the `development` branch.