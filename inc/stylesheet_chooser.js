function extractComponentsOfRepo(repo) {
	var result = new Array();
	var re = new RegExp("github.com/([^/]+)/([^/]+).*");
	var m = re.exec(repo);
	if (m != null) {
		result["user"]=m[1];
		result["repo"]=m[2];
	}
	return result;
}

function githubApiList(user, repo, path) {
	return "https://api.github.com/repos/" + user + "/" + repo + "/contents/" + path;
}

function listDir(user, repo, path, callback) {
	var url = githubApiList(user, repo, path);
	$.get(url, function(data) {
		callback(data);
	})
	.fail(function() { alert("unable to get "+path); });
}

/**
 * Uses jquery to load asynchronously the various stylesheets defined in github and their associated home.png
 */
function loadStylesheetsFromGitHub() {
	var githubRepo = $('#githubRepo').val();
	// parse repo to get user name, repo name ...
	var connectionComponents = extractComponentsOfRepo(githubRepo);
	var user = connectionComponents["user"];
	var repo = connectionComponents["repo"];
	listDir(user, repo, "", function(data) {
		if(data!=null && data.length>0) {
			for(var i= 0; i < data.length; i++) {
				folderInfo = data[i];
				if(folderInfo.name=="themes" && "dir"==folderInfo.type) {
					listDir(user, repo, folderInfo.path, readThemesDir(user, repo));
				}
			}
		} else {
			alert("path doesn't seem to indicate a valid shaarli css repository. please use nodiscc one instead");
		}
	});
}

function readThemesDir(user, repo) {
	return function(data) {
		if(data!=null && data.length>0) {
			for(var i= 0; i < data.length; i++) {
				themeFolderInfo = data[i];
				if("dir"==themeFolderInfo.type) {
					var themePath = themeFolderInfo.path;
					listDir(user, repo, themePath, readThemeContent(themeFolderInfo.name));
				}
			}
		} else {
			alert("path doesn't seem to indicate a valid shaarli css repository. please use nodiscc one instead");
		}
	}
}

function readThemeContent(themeName) {
	return function(data) {
		if(data!=null && data.length>0) {
			var themeData = new Array();
			themeData['name'] = themeName;
			var usable = false;
			for(var i= 0; i < data.length; i++) {
				themeFileInfo = data[i];
				if("file"==themeFileInfo.type) {
					if("user.css"==themeFileInfo.name) {
						themeData['css'] = themeFileInfo.url;
						usable = true;
					} else if("home.png"==themeFileInfo.name) {
						themeData['image'] = themeFileInfo.url;
					}
				}
			}
			if(usable) {
				addThemeToSelector(themeData);
			}
		}
	}
}

function addThemeToSelector(themeData) {
	if($("#stylesheet option:[data-name='"+themeData["name"]+"']").length==0) {
		$('#stylesheet').append("<option value='"+themeData["css"]+"' data-name='"+themeData["name"]+"' data-image='"+themeData["image"]+"'>"+themeData["name"]+"</option>");
	}
}

function onChangeStyleSheet() {
	var select = $('#stylesheet').get(0);
	var selectedIndex = select.selectedIndex;
	var preview = $('#stylesheet_preview').get(0);
	if(selectedIndex==0) {
		preview.src=null;
	} else {
		imageUrl = select.options[selectedIndex].getAttribute('data-image');
		$.get(imageUrl, function(data) {
			var imageContent = 'data:image/png;base64,'+data.content;
			preview.src = imageContent;
		});
	}
}


var stylesheetRepositoryDownloadUrl = "";

function styleSheetSourceUrlChanged() {
	var githubRepo = $('#githubRepo').val();
	if(stylesheetRepositoryDownloadUrl!=githubRepo) {
		loadStylesheetsFromGitHub();
		stylesheetRepositoryDownloadUrl = githubRepo;
	}
}

// OK, this may not be easy to other scripts, but I needed to add a ready handler to have the list loaded immediatly
$(document).ready(function () {
	setInterval(styleSheetSourceUrlChanged, 1000);
});