const fs = require('fs');
let code = fs.readFileSync('src/components/modals/FaceAttendanceModal.tsx', 'utf8');
code = code.replace(/\r\n/g, '\n');

const anchor = `<div className="space-y-2">
                  {(faceStatus?.has_assigned_office === false || !faceStatus?.office_config) && (`;

const newBanners = `<div className="space-y-2">
                  {(!faceStatus?.is_enrolled || faceStatus?.approval_status === 2) && (
                    <div className="p-2.5 rounded-xl bg-amber-950/40 border border-amber-500/50 text-amber-200 text-xs flex items-center justify-between space-x-2">
                      <div className="flex items-center space-x-2">
                        <UserCheck className="w-4 h-4 text-amber-400 shrink-0" />
                        <p className="text-[11px] leading-tight">
                          Face ID registration required before marking attendance.
                        </p>
                      </div>
                      <button
                        type="button"
                        onClick={() => setMode('enroll')}
                        className="px-2.5 py-1 text-[10px] font-bold bg-amber-500 hover:bg-amber-400 text-zinc-950 rounded-lg shrink-0 cursor-pointer active:scale-95 transition-all shadow"
                      >
                        Register Face
                      </button>
                    </div>
                  )}

                  {faceStatus?.is_enrolled && (faceStatus?.approval_status === 0 || !faceStatus?.is_approved) && (
                    <div className="p-2.5 rounded-xl bg-amber-950/40 border border-amber-500/50 text-amber-200 text-xs flex items-center space-x-2">
                      <Clock className="w-4 h-4 text-amber-400 shrink-0 animate-pulse" />
                      <p className="text-[11px] leading-tight">
                        Face ID is pending Admin approval in CRM. Attendance is locked until Admin approves your registered face.
                      </p>
                    </div>
                  )}

                  {biometricComparison && !biometricComparison.match && (
                    <div className="p-2.5 rounded-xl bg-rose-950/60 border border-rose-500/70 text-rose-200 text-xs flex items-center space-x-2">
                      <AlertCircle className="w-4 h-4 text-rose-400 shrink-0" />
                      <p className="text-[11px] leading-tight">
                        <strong className="text-rose-300">Biometric Mismatch ({biometricComparison.similarity}%):</strong> Live face does not match {userName}&apos;s registered Face ID. Proxy attendance is blocked!
                      </p>
                    </div>
                  )}

                  {(faceStatus?.has_assigned_office === false || !faceStatus?.office_config) && (`;

if (code.includes(anchor)) {
  code = code.replace(anchor, newBanners);
  fs.writeFileSync('src/components/modals/FaceAttendanceModal.tsx', code, 'utf8');
  console.log('Added face security banners to FaceAttendanceModal.tsx successfully!');
} else {
  console.log('Anchor still not found in normalized code');
}
