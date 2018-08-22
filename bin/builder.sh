
version=1.0.0

function cleanAndPackage()
{
    sudo cp -Rf src/ bin/package/hipay-enterprise-sdk-woocommerce
    cd bin/package/ && zip -r hipay-enterprise-sdk-woocommerce-$version.zip hipay-enterprise-sdk-woocommerce
    sudo rm -R hipay-enterprise-sdk-woocommerce
}

function show_help()
{
	cat << EOF
Usage: $me [options]
options:
    -h, --help                        Show this help
    -v, --version                     Configure version for package
EOF
}

function parse_args()
{
	while [[ $# -gt 0 ]]; do
		opt="$1"
		shift

		case "$opt" in
			-h|\?|--help)
				show_help
				exit 0
				;;
				esac
		case "$opt" in
			-v|--version)
              	version="$1"
				shift
				;;
		    esac
	done;
}

function main()
{
	parse_args "$@"
	cleanAndPackage
}

main "$@"