/**
 * Renderer graphic history.
 *
 * @param {Array} commits contains commits with id and parentId
 * @param {Integer} topPadding top graph padding
 * @param {Integer} leftPadding left graph padding
 * @param {Integer} columnWidth width of column
 * @param {Integer} commitRadius commit dot radius
 */
var HistoryGraph = function(options) {
    $(document).ready(function() {
        var $simple = $('#historySimple');
        var $simpleItems = $('#historySimple .js-history-simple-item');
        var $graph = $('#historyGraph');
        $graph.css({
            'float': 'left',
            'position': 'absolute',
            'top': $simple.offset().top - 2,
            'left': 0
        });

        var canvas = Raphael('historyGraph', 0, 0);

        var commitHeight = $simpleItems.eq(0).outerHeight(true);
        var commitWidth = options.columnWidth;
        var leftPadding = options.leftPadding;
        var topPadding = options.topPadding;

        var maxLeft = leftPadding;

        var colors = [
            '#f00', '#00f', '#0f0',
            '#0ff', '#00f', '#f0f',
            '#0ff', '#00f', '#f0f',
        ];

        // create commits array
        var commits = {};
        for (var i = 0; i < options.commits.length; i++) {
            var x = leftPadding + options.commits[i].level * commitWidth + (commitWidth / 2);
            var y = topPadding + (i * commitHeight) + (commitHeight / 2);
            var color = colors[options.commits[i].level % colors.length];
            commits[options.commits[i].id] = {
                'cx': x,
                'cy': y,
                'color': color,
                'level': options.commits[i].level,
                'parents': options.commits[i].parents
            };
        }

        // render lines
        for (var i in commits) {
            var commit = commits[i];
            for (var k in commit.parents) {
                var parentId = commit.parents[k];
                if (typeof commits[parentId] !== 'undefined') {
                    canvas.path([
                        'M', commits[parentId].cx, commits[parentId].cy,
                        'L', commit.cx, commit.cy
                    ]).attr({
                        'stroke': commit.color
                    });
                }
            }
        }

        // render commits
        for (var i in commits) {
            var commit = commits[i];
            canvas.circle(commit.cx, commit.cy, options.commitRadius).attr({
                'fill': commit.color,
                'stroke': commit.color
            });
            maxLeft = Math.max(maxLeft, commit.cx + (options.commitRadius / 2));
        }

        canvas.setSize(maxLeft + leftPadding, $simpleItems.last().offset().top + $simpleItems.last().outerHeight() - $simple.offset().top);
        canvas.setViewBox(0, 0, maxLeft + leftPadding, $simpleItems.last().offset().top + $simpleItems.last().outerHeight() - $simple.offset().top, true);
    });
};
