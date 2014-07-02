/**
 * Created by j.calabrese on 02/07/14.
 */
function (doc) {
    pmdNb = 0;
    phpUnitCoverage = 0;
    if (doc.stats.pmd != null) {
        pmdNb = doc.stats.pmd.length;
    } else {
        phpUnitCoverage = 0;
    }
    if (doc.stats.phpUnit != null && doc.stats.phpUnit.lineAverage != null) {
        phpUnitCoverage = doc.stats.phpUnit.lineAverage;
    } else {
        phpUnitCoverage = 0;
    }
    emit([doc.bundle, pmdNb, phpUnitCoverage], doc);
}