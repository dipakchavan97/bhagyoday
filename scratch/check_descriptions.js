const https = require('https');

const slugs = [
    'ashwagandha-shilajit-men-wellness-capsules',
    'karela-jamun-gurmar-sugar-balance-capsules',
    'arjuna-amrit-heart-vitality-juice'
];

slugs.forEach(slug => {
    https.get(`https://lightgreen-heron-113083.hostingersite.com/product/${slug}/`, (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => {
            console.log(`\n=================== ${slug} ===================`);
            const descStart = data.indexOf('ayur-description-content');
            if (descStart !== -1) {
                const descEnd = data.indexOf('</section>', descStart);
                console.log(data.substring(descStart, descEnd));
            } else {
                console.log('Description content not found');
            }
        });
    });
});
