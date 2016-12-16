class CssSplitter

  def self.split(infile, outdir = File.dirname(infile), max_selectors = 4095)

    raise "infile could not be found" unless File.exists? infile

    rules = IO.readlines(infile, "}")
    return if rules.first.nil?

    charset_statement, rules[0] = rules.first.partition(/^\@charset[^;]+;/)[1,2]
    return if rules.nil?

    # The infile remains the first file
    file_id = 1
    selectors_count = 0
    output = nil

    selectors_count = rules.map { |rule| count_selectors_of_rule rule }.inject(:+)

    # Nothing happens until the selectors limit is reached for the first time
    if selectors_count > max_selectors

      # Reset selectors count
      selectors_count = 0

      # Create first file with less than maximum selectors
      output = create_new_file(file_id, outdir, infile, charset_statement)

      rules.each do |rule|

        rule_selectors_count = count_selectors_of_rule rule
        selectors_count += rule_selectors_count

        if selectors_count > max_selectors
          # Close current file if there is already one
          output.close if output
          # Prepare next file
          file_id += 1
          output = create_new_file(file_id, outdir, infile, charset_statement)
          # Reset count with current rule count
          selectors_count = rule_selectors_count
        end

        output.write rule if output
      end
      output.close if output
    end
  end

  def self.create_new_file(file_id, outdir, infile, charset_statement)
    if file_id == 1
      filename = File.join(outdir, File.basename(infile, File.extname(infile)) + File.extname(infile))
    else
      filename = File.join(outdir, File.basename(infile, File.extname(infile)) + "#{file_id.to_s}" + File.extname(infile))
    end
    output = File.new(filename, "w")
    output.write charset_statement
    output
  end

  def self.count_selectors_of_rule(rule)
    rule.partition(/\{/).first.scan(/,/).count.to_i + 1
  end

end