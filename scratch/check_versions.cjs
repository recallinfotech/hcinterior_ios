const fs = require('fs');

// Check android build.gradle
const gradle = fs.readFileSync('android/app/build.gradle', 'utf8');
const vcMatch = gradle.match(/versionCode\s+(\d+)/);
const vnMatch = gradle.match(/versionName\s+['"]([^'"]+)['"]/);
console.log('Android versionCode:', vcMatch ? vcMatch[1] : 'not found');
console.log('Android versionName:', vnMatch ? vnMatch[1] : 'not found');

// Check ios project.pbxproj
const pbx = fs.readFileSync('ios/App/App.xcodeproj/project.pbxproj', 'utf8');
const pbxMatches = [...pbx.matchAll(/(CURRENT_PROJECT_VERSION|MARKETING_VERSION)\s*=\s*([^;]+);/g)];
pbxMatches.forEach(m => console.log('iOS ' + m[1] + ' = ' + m[2].trim()));
