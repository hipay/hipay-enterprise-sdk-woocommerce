
version=1.0.0

function cleanAndPackage()
{
    sudo cp -Rf src/woocommerce_hipayenterprise bin/package/hipay-enterprise-sdk-woocommerce
    cd bin/package/

    ############################################
    #####          CLEAN IDEA FILE           ####
    ############################################
    if [ -d hipay_enterprise/nbproject ]; then
        rm -R hipay-enterprise-sdk-woocommerce/nbproject
    fi

    if [ -d hipay_enterprise/.idea ]; then
        rm -R hipay-enterprise-sdk-woocommerce/.idea
    fi

    find hipay-enterprise-sdk-woocommerce/ -type d -exec cp index.php {} \;

    zip -r hipay-enterprise-sdk-woocommerce-$version.zip hipay-enterprise-sdk-woocommerce
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
