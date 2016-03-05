# Iris9700HD02
Some PHP script files to ease Iris 9700 HD 02 config updates

cccam.php: Most cccam lines i find over Internet  are in the following format which is as far as i know are not compatible with the .cfg file needed to update cccam config on my Iris 9700 HD 02. So running this script like "php cccam.php --input path/to/your/cccam/files --output output/path" will read the input files and generate a .cfg file similar to <NETDBS_TXT_VER_1>
CCCAM:{s3.cccam-free.com}{11000}{nngins}{cccam-free.com}{2}
