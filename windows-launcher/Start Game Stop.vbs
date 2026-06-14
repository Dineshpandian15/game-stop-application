' Opens Game Stop without showing a black command window.
Set shell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
launcher = fso.GetParentFolderName(WScript.ScriptFullName)
shell.CurrentDirectory = launcher
shell.Run """" & launcher & "\Game Stop.bat""", 0, False
