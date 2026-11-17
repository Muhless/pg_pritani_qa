package id.pritani.e2e.support;

public final class XPath {

    private XPath() {
    }

    /**
     * Builds an XPath string literal that is safe for values containing quotes.
     */
    public static String literal(String value) {
        if (!value.contains("'")) {
            return "'" + value + "'";
        }

        if (!value.contains("\"")) {
            return "\"" + value + "\"";
        }

        return "concat('" + value.replace("'", "', \"'\", '") + "')";
    }
}
