FIXTURE_DIR="./fixtures"
FIXTURE_DATA="$FIXTURE_DIR/data $FIXTURE_DIR/uploads"
NAME=$1
FILE="$NAME.tar.gz"
if [[ -z "$NAME" ]]; then
	echo "needs one argument (fixture name)"
	exit 1	
elif [[ ! -f "$FIXTURE_DIR/$FILE" ]]; then
	echo "fixture does not exist"
	exit 1
else
		rm -rf "$FIXTURE_DATA"
		cd "$FIXTURE_DIR" || exit 1
		tar -xvf "$FILE"
		echo "Done."
		exit 0
fi
