# Instrument photography

Use our own photographs or photographs specifically authorized by the supplier for our website. A general manufacturer agreement is useful background; retain the permission and identify the exact files it covers before approving an image. No instrument photographs or SKU mappings have been supplied with this integration. The default manifest is empty, so the site shows no instrument photograph until one is approved.

For each exact SKU, request:

- A clear full front view and side view against a neutral background, with the complete instrument in frame.
- A close view of the working end and any distinguishing handle, teeth, curve, graduation or locking mechanism.
- A separate scale view using a ruler in the same plane. Keep the exact SKU and supplier reference in the private shoot record; do not infer dimensions from perspective.
- The original files and permission record, plus a proposed public credit if attribution is required. Confirm website use, cropping/resizing and any restrictions with the rights holder.

Match the physical instrument, SKU and visible details before approval. A photograph of one variant must not be described as every variant in its family: label family-level photography **“Shown: variant {SKU}”**. Photos do not establish alloy, material grade, performance, certification, sterilization compatibility or other specifications. Those facts require their own evidence and product review.

Prepare a web-sized JPG, PNG or WebP with a simple filename (letters, digits, underscores and hyphens). Remove embedded private metadata, including location/contact details, before the final review. Keep raw originals, supplier correspondence and unapproved photographs outside the website. Only approved delivery images belong in `/uploads/instruments/`, since files there are publicly accessible even when no template displays them.

## Private manifest

Set `SIAL_INSTRUMENT_MEDIA_FILE` to the absolute path of a JSON file outside the site and web server document root. Start with:

```json
{"schema_version":1,"images":[]}
```

Each image entry has these fields; the identity and file values below are placeholders, not an approved mapping:

```json
{
  "family_code": "EXACT-FAMILY-CODE",
  "sku": "EXACT-EXISTING-SKU",
  "file": "approved-variant-front.jpg",
  "sha256": "SHA256-OF-THE-FINAL-DELIVERY-FILE",
  "approved": false,
  "rights": {
    "basis": "supplier-authorized",
    "evidence_ref": "private record identifying permission and covered files",
    "checked_by": "private reviewer identifier",
    "checked_on": "YYYY-MM-DD"
  },
  "alt": "Concise visible description of this exact variant",
  "caption": "Optional public description",
  "credit": "Only the credit explicitly cleared for public display",
  "view": "front"
}
```

Use `basis` of `own` or `supplier-authorized`. The reviewer sets `approved` to the JSON boolean `true` only after checking the exact variant, public text, final image bytes and rights record. Compute the lowercase SHA-256 of the final delivery file, for example `shasum -a 256 approved-variant-front.jpg`. A changed image must be reviewed and its hash updated; the previous approval stops rendering if the bytes change. Approval of a photo does not approve the associated catalogue specifications.

`view` supports `front`, `side`, `working-end`, `handle`, `scale`, `detail` and `packaging`. `file` is a basename ending in lowercase `.jpg`, `.png` or `.webp`; paths, remote URLs and SVG are rejected. The helper checks the actual raster format, dimensions and GD decoding, with limits of 15 MB, 12,000 pixels per dimension and 12 megapixels. PHP GD with the relevant image format support is required; unsupported images stay hidden. An unreadable or malformed manifest also shows no images. Invalid individual entries are omitted.

`instrument_media_for_family($family)` returns a map of existing SKU to image lists. Pass `true` as its second argument for a representative-only card: validation stops at the first approved photo instead of decoding the whole family's gallery. `instrument_media_for_product($family, $product)` returns only that product's images, with no substitute from another variant. Each returned image contains only `path`, `alt`, `caption`, `credit`, `view`, `width` and `height`; escape the text when rendering and keep the associated SKU visible at family level. The public image path carries a short content-version query so reapproved replacement bytes use a fresh browser cache entry. Supplier names, permission references, reviewer identities and the rest of the manifest remain private unless someone explicitly writes information into an approved public caption or credit.

Keep this workflow document and the site's tests inaccessible through the web server. To revoke a photograph, remove its manifest approval and remove the public delivery file (including any deployed copies); a hidden template alone cannot revoke a directly accessible file.
