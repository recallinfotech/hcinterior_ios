const fs = require('fs');
let code = fs.readFileSync('src/services/userPortalApi.ts', 'utf8');

// Add liveDescriptor to MarkFaceAttendanceParams
if (!code.includes('liveDescriptor?:')) {
  code = code.replace(
    'isInsideGeofence?: boolean;\n}',
    'isInsideGeofence?: boolean;\n  liveDescriptor?: number[] | string;\n}'
  );
}

// Add live_descriptor to formData in markFaceAttendanceApi
if (!code.includes("formData.append('live_descriptor'")) {
  code = code.replace(
    "formData.append('is_inside_geofence', params.isInsideGeofence ? '1' : '0');",
    "formData.append('is_inside_geofence', params.isInsideGeofence ? '1' : '0');\n  if (params.liveDescriptor) formData.append('live_descriptor', typeof params.liveDescriptor === 'string' ? params.liveDescriptor : JSON.stringify(params.liveDescriptor));"
  );
}

fs.writeFileSync('src/services/userPortalApi.ts', code, 'utf8');
console.log('Updated userPortalApi.ts with liveDescriptor support!');

