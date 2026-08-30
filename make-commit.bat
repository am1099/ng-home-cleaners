@echo off
cd /d "C:\Users\abdal\Desktop\Web Development Projects\ng-home-cleaners"
"C:\Program Files\Git\cmd\git.exe" add -A
"C:\Program Files\Git\cmd\git.exe" reset HEAD -- .cursor >nul 2>&1
"C:\Program Files\Git\bin\git.exe" write-tree > .git\TREE.txt
set /p TREE=<.git\TREE.txt
"C:\Program Files\Git\bin\git.exe" commit-tree %TREE% -m "Initial commit: NG Home Cleaners Laravel app for Laravel Cloud." > .git\COMMIT.txt
set /p COMMIT=<.git\COMMIT.txt
echo TREE=%TREE%
echo COMMIT=%COMMIT%
"C:\Program Files\Git\bin\git.exe" update-ref refs/heads/main %COMMIT%
"C:\Program Files\Git\bin\git.exe" symbolic-ref HEAD refs/heads/main
"C:\Program Files\Git\bin\git.exe" log -1 --oneline
"C:\Program Files\Git\bin\git.exe" status -sb
