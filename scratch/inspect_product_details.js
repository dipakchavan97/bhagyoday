async function check() {
    const res = await fetch('https://lightgreen-heron-113083.hostingersite.com/product/ashwagandha-shilajit-men-wellness-capsules-800mg-30-capsules/');
    const html = await res.text();
    
    const sections = [];
    const re = /<h3 class="ayur-detail-heading">([^<]+)<\/h3>\s*<div class="ayur-detail-body">([\s\S]*?)<\/div>/gi;
    let m;
    while ((m = re.exec(html)) !== null) {
        sections.push({
            heading: m[1].trim(),
            content: m[2].replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim()
        });
    }
    console.log(JSON.stringify(sections, null, 2));

    // Check if KSM-66 appears anywhere in the HTML
    const ksmMatches = Array.from(html.matchAll(/.{0,40}KSM-66.{0,40}/gi)).map(x => x[0].trim());
    console.log('KSM-66 occurrences:', ksmMatches);
}

check().catch(console.error);
